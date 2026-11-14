<?php

namespace CryptoNoises\Commands;

use CryptoNoises\Config;
use CryptoNoises\Models;

trait JobsController_RecoveryDeadJobs
{
    public function recoverDeadJobs(): void
    {
        $this->logMsg("Start");

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $count = Models\FixerTezosTransactionsAnalyze::model()->recoverDeadJobs(Config::kJOBS_TIMEOUT_TEZOS_TRANSACTION_ANALYZE);
        if ($count > 0)
        {
            $this->logMsg("FixerTezosTransactionsAnalyze: recovered $count job(s)");
            Models\FixerEvents::model()->fix('jobs.recovery', 'FixerTezosTransactionsAnalyze', $count);
        }
        else
            $this->logMsg("FixerTezosTransactionsAnalyze: zombies not found");

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->findZombies('Queues\QueueDomainsExpiry', 5*60, Models\Queues\QueueDomainsExpiry::model());

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->logMsg("Done");
    }

    private function findZombies(string $scope, int $zombieTime, Models\Queues\QueueAbstract $model): void
    {
        $taskItems = $model->getDeadSyncingTasks($zombieTime);

        if (empty($taskItems))
        {
            $this->logMsg("$scope: zombies not found");
            return;
        }

        foreach ($taskItems as $taskItem)
        {
            $this->logMsg("$scope: #{$taskItem['queue_task_id']}");

            $model->postponeOrCloseTask($taskItem, null, 'Zombie recovered', +1);

            Models\FixerEvents::model()->fix('jobs.recovery', $scope, $taskItem['queue_task_id']);
        }
    }
}
