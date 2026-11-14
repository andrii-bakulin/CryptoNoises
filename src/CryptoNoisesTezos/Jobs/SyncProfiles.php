<?php

namespace CryptoNoisesTezos\Jobs;

use CryptoNoisesTezos\Models\Queues\QueueProfiles;
use CryptoCore\Enum\JobResult;

class SyncProfiles extends AbstractJob
{
    use SyncProfiles_Tzkt;
    use SyncProfiles_Tzprofiles;

    public function execute(array $taskItem): JobResult
    {
        $this->initJob();

        switch ($taskItem['profile_type'])
        {
            case QueueProfiles::kPROFILE_TYPE_tzkt:             return $this->syncTzkt($taskItem);
            case QueueProfiles::kPROFILE_TYPE_tzprofiles:       return $this->syncTzprofiles($taskItem);
        }

        // Unknown profile type: close the task instead of killing the worker
        $this->lastActionNotice = 'Unknown profile_type ['.$taskItem['profile_type'].']';
        return JobResult::FailedAndClose();
    }
}
