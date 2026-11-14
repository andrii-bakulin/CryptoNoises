<?php

namespace CryptoCore\Commands;

use CryptoCore\Enum\JobResult;
use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use VssCfg;

trait JobsController_Executor
{
    protected function startJob(string                                  $jobId, string $jobLockerId,
                                \CryptoCore\Models\Queues\QueueAbstract $queueModel,
                                \CryptoCore\Jobs\AbstractJob            $jobInstance,
                                int                                     $threadIdx, int $threadCnt, int $limit, int $timeLimit, int $timeLimitWithSafeReserve)
    {
        VssCfg::cfg()->pushErrorReportingScope($jobId);

        $tsStart = Time::Microtime();

        ini_set('memory_limit', '512M');
        set_time_limit($timeLimitWithSafeReserve);

        if (!$this->checkLockAndWaitUntilNoOtherInstanceRuns($jobLockerId, $timeLimit))
            return;

        $jobInstance->verbose = $this->verbose;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        while (true)
        {
            Vss::dbCloseAll();

            if (Time::Microtime() - $tsStart > $timeLimit)
                break;

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $tsJobStart = Time::Microtime();

            $taskItem = $queueModel->getNextTask($threadIdx, $threadCnt);

            if ($taskItem == null)
            {
                $this->logMsg("Sleep for 1 sec");
                sleep(1);
                continue;
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            VssCfg::cfg()->pushErrorReportingScope($taskItem['queue_task_id']);

            $jobParams = '';
            array_walk($taskItem, function($val, $key) use(&$jobParams)
            {
                if (in_array($key, [
                    'prev_queue_task_id',
                    'state','sync_mode','priority','iteration',
                    'ts_created','ts_activate','ts_updated','exec_time','last_notice'
                ]))
                    return;

                $jobParams .= "$key=$val; ";
            });

            $this->logMsg("Job[$jobId]: ".$jobParams);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $queueModel->openSyncProcess($taskItem);

            $result = $jobInstance->execute($taskItem);

            $jobExecTime = Time::Microtime() - $tsJobStart;

            switch ($result->code())
            {
                case JobResult::kCompleted:
                    $queueModel->closeTaskOnSuccess($taskItem, $jobExecTime, $jobInstance->lastActionNotice);
                    $this->logMsg("Success: ".$jobInstance->lastActionNotice);
                    break;

                case JobResult::kFailedAndPostpone:
                    $queueModel->postponeOrCloseTask($taskItem, $jobExecTime, $jobInstance->lastActionNotice, +1);
                    $this->logMsg("Failed: ".$jobInstance->lastActionNotice);
                    break;

                case JobResult::kFailedAndRepeat:
                    $queueModel->postponeOrCloseTask($taskItem, $jobExecTime, $jobInstance->lastActionNotice, 0);
                    break;

                case JobResult::kFailedAndClose:
                    $queueModel->postponeOrCloseTask($taskItem, $jobExecTime, $jobInstance->lastActionNotice, -1);
                    $this->logMsg("Failed: ".$jobInstance->lastActionNotice);
                    break;
            }

            VssCfg::cfg()->popErrorReportingScope();

            if ($limit > 0 && --$limit == 0)
                break;
        }

        $this->logMsg("END.");
    }

    protected function startJobOnce(string                       $jobId,
                                    \CryptoCore\Jobs\AbstractJob $jobInstance)
    {
        VssCfg::cfg()->pushErrorReportingScope($jobId);

        ini_set('memory_limit', '512M');
        set_time_limit(15*60);

        $jobInstance->verbose = $this->verbose;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->logMsg("Job #[$jobId] exec once");

        $result = $jobInstance->execute([]);

        $this->logMsg("END.");
    }
}
