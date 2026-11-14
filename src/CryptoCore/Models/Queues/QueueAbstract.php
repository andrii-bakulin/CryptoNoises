<?php

namespace CryptoCore\Models\Queues;

use CryptoCore\Vss\VssDb;
use CryptoCore\Vss\VssModel;
use CryptoCore\Vss\VssDbExp;

abstract class QueueAbstract extends VssModel
{
    const kState_Idle               = 'idle';
    const kState_Syncing            = 'syncing';
    const kState_Success            = 'success';
    const kState_Error              = 'error';
    const kSyncMode_New             = 'new';
    const kSyncMode_Update          = 'update';

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Select modes

    const kSELECT_MODE_FirstInFirstOut          = 'first-in-first-out';
    const kSELECT_MODE_Normal                   = 'normal';
    const kSELECT_MODE_Anyone                   = 'anyone';

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public string $selectMode                   = self::kSELECT_MODE_Normal;
    public int $maxTryCount                     = 16;

    abstract public function tableName(): string;

    abstract protected function dbConnection(): VssDb;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @param array $params
     * @param string|null $syncMode
     * @param int $priority             {1..9} - 1=HIGHEST .. 9=LOWEST
     * @return void
     */
    public function push(array $params, ?string $syncMode = null, int $priority = 5): void
    {
        $tableName = $this->tableName();

        if (isset($params['state']) && $params['state'] == self::kState_Error)
            $tableName .= '__failed';

        $params['priority']  = $priority;
        $params['sync_mode'] = $syncMode;

        $this->dbConnection()->insert($tableName, $params, 'IGNORE');
    }

    public function getNextTask(int $threadIndex, int $threadCount): ?array
    {
        if ($this->selectMode == self::kSELECT_MODE_FirstInFirstOut)
        {
            $sql = "
                SELECT *
                FROM {$this->tableName()}
                WHERE state = :state
                    AND ts_activate <= NOW()
                    ".($threadCount > 1 && $threadIndex < $threadCount ? " AND queue_task_id % $threadCount = $threadIndex " : '' )."
                ORDER BY queue_task_id
                LIMIT 1
            ";
        }
        else if ($this->selectMode == self::kSELECT_MODE_Anyone)
        {
            $sql = "
                SELECT *
                FROM {$this->tableName()}
                WHERE state = :state
                    AND ts_activate <= NOW()
                    ".($threadCount > 1 && $threadIndex < $threadCount ? " AND queue_task_id % $threadCount = $threadIndex " : '' )."
                LIMIT 1
            ";
        }
        else // self::kSELECT_MODE_Normal (default) and any unknown mode
        {
            $sql = "
                SELECT *
                FROM {$this->tableName()}
                WHERE state = :state
                  AND ts_activate <= NOW()
                      ".($threadCount > 1 && $threadIndex < $threadCount ? " AND queue_task_id % $threadCount = $threadIndex " : '' )."
                ORDER BY priority, iteration, RAND()
                LIMIT 1
            ";
        }

        return $this->dbConnection()->queryRow($sql, ['state'=>self::kState_Idle]);
    }

    public function getDeadSyncingTasks(int $timeout): array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE state = :state_syncing
              AND ts_updated < NOW() - INTERVAL :timeout SECOND
        ";

        return $this->dbConnection()->query($sql, [
            'state_syncing' => self::kState_Syncing,
            'timeout'       => $timeout,
        ]);
    }

    public function openSyncProcess(array $taskItem): void
    {
        $this->updateTaskState($taskItem, self::kState_Syncing);
    }

    public function updateTaskState(array $taskItem, string $newState, ?float $jobExecTime = null, ?string $notice = null, array $extraDataUpdate = []): void
    {
        $data = $extraDataUpdate;
        $data['state']       = $newState;
        $data['last_notice'] = $notice;

        if ($jobExecTime > 0)
            $data['exec_time'] = $jobExecTime;

        $this->dbConnection()->update($this->tableName(), $data, ['queue_task_id' => $taskItem['queue_task_id']]);
    }

    public function closeTaskOnSuccess(array $taskItem, ?float $jobExecTime = null, ?string $notice = null): void
    {
        $this->updateTaskState($taskItem, self::kState_Success, $jobExecTime, $notice);
        $this->moveTaskToExtendedTable($taskItem['queue_task_id'], '__done');
    }

    /**
     * iterationUpdate:  1 = increase by 1, 0 = keep, -1 = close the task
     */
    public function postponeOrCloseTask(array $taskItem, ?float $jobExecTime = null, ?string $notice = null, int $iterationUpdate = 1): void
    {
        $newIteration = $taskItem['iteration'];

        if ($iterationUpdate > 0)
            $newIteration += $iterationUpdate;

        if ($iterationUpdate < 0 || $newIteration >= $this->maxTryCount)
        {
            $this->updateTaskState($taskItem, self::kState_Error, $jobExecTime, $notice);
            $this->moveTaskToExtendedTable($taskItem['queue_task_id'], '__failed');
        }
        else
        {
            $this->updateTaskState($taskItem, self::kState_Idle, $jobExecTime, $notice, [
                'iteration'     => $newIteration,
                'ts_activate'   => VssDbExp::now($newIteration*$newIteration * 60),
            ]);
        }
    }

    public function moveTaskToExtendedTable(int $queueTaskId, string $tableSuffix): void
    {
        $item = $this->dbConnection()->selectRow('*', $this->tableName(), ['queue_task_id' => $queueTaskId]);

        if ($item == null)
            return;

        $item['prev_queue_task_id'] = $item['queue_task_id'];
        unset($item['queue_task_id']);

        $this->dbConnection()->insert($this->tableName().$tableSuffix, $item, 'IGNORE');

        $this->dbConnection()->delete($this->tableName(), ['queue_task_id' => $queueTaskId]);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
}
