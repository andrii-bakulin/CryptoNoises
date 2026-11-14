<?php

namespace CryptoNoises\Jobs;

use CryptoCore\Enum\JobResult;
use CryptoNoises\Models;
use Exception;

class DomainsExpiryCleanup extends AbstractJob
{
    public function execute(array $taskItem): JobResult
    {
        $this->initJob();

        try
        {
            Models\FixerDomainsExpiry::model()->cleanup();

            return JobResult::Completed();
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();

            return JobResult::FailedWithCode($e->getCode());
        }
    }
}
