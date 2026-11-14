<?php

namespace CryptoNoisesTezos\Commands;

use CryptoNoisesTezos\Config;
use CryptoNoisesTezos\Models;

trait JobsController_RecoveryDeadJobs
{
    public function recoverDeadJobs(): void
    {
        $this->logMsg("Start");

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $count = Models\FixerTransactionsSync::model()->recoverDeadJobs(Config::kJOBS_TIMEOUT_TRANSACTION_SYNC);
        if ($count > 0)
        {
            $this->logErr("FixerTransactionsSync: recovered $count job(s)");
            Models\FixerEvents::model()->fix('jobs.recovery', 'FixerTransactionsSync', $count);
        }
        else
            $this->logMsg("FixerTransactionsSync: zombies not found");

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $count = Models\FixerBigmapUpdatesSync::model()->recoverDeadJobs(Config::kJOBS_TIMEOUT_BIGMAP_UPDATE_SYNC);
        if ($count > 0)
        {
            $this->logErr("FixerBigmapUpdatesSync: recovered $count job(s)");
            Models\FixerEvents::model()->fix('jobs.recovery', 'FixerBigmapUpdatesSync', $count);
        }
        else
            $this->logMsg("FixerBigmapUpdatesSync: zombies not found");

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $count = Models\FixerBigmapUpdatesAnalyze::model()->recoverDeadJobs(Config::kJOBS_TIMEOUT_BIGMAP_UPDATE_ANALYZE);
        if ($count > 0)
        {
            $this->logErr("FixerBigmapUpdatesAnalyze: recovered $count job(s)");
            Models\FixerEvents::model()->fix('jobs.recovery', 'FixerBigmapUpdatesAnalyze', $count);
        }
        else
            $this->logMsg("FixerBigmapUpdatesAnalyze: zombies not found");

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->findZombies('Queues\QueueContracts', 5*60, Models\Queues\QueueContracts::model());

        $this->findZombies('Queues\QueueTokensDetected', 5*60, Models\Queues\QueueTokensDetected::model());

        $this->findZombies('Queues\QueueTokensMetadata', 5*60, Models\Queues\QueueTokensMetadata::model(), [
            'metadata_state' => Models\Tokens::kMETADATA_STATE_Idle,
            'error_msg'      => 'Metadata-zombie recovered',
        ]);

        $this->findZombies('Queues\QueueTokensMetadataMerge', 5*60, Models\Queues\QueueTokensMetadataMerge::model());

        $this->findZombies('Queues\QueueIpfsMedia', 5*60, Models\Queues\QueueIpfsPrimary::model(), [
            'preview_state' => Models\Tokens::kPREVIEW_STATE_Idle,
            'error_msg'     => 'Preview-zombie recovered (primary)',
        ]);

        $this->findZombies('Queues\QueueIpfsMedia', 5*60, Models\Queues\QueueIpfsSecondary::model(), [
            'preview_state' => Models\Tokens::kPREVIEW_STATE_Idle,
            'error_msg'     => 'Preview-zombie recovered (secondary)',
        ]);

        $this->findZombies('Queues\QueueProfiles', 5*60, Models\Queues\QueueProfiles::model());

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->logMsg("Done");
    }

    private function findZombies(string $scope, int $zombieTime, Models\Queues\QueueAbstract $model, ?array $tokenTableUpdate = null): void
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

            if ($tokenTableUpdate)
            {
                Models\Tokens::model()->update($taskItem['contract_adid'], $taskItem['token_unid'], $tokenTableUpdate);
            }

            $model->postponeOrCloseTask($taskItem, null, 'Zombie recovered', +1);

            Models\FixerEvents::model()->fix('jobs.recovery', $scope, $taskItem['queue_task_id']);
        }
    }
}
