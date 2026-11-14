<?php

namespace CryptoCore\Jobs;

use CryptoCore\Enum\JobResult;

abstract class AbstractJob
{
    public bool $verbose = false;

    public ?string $lastActionNotice = null;

    /**
     * @param array $taskItem
     * @return JobResult
     */
    abstract public function execute(array $taskItem): JobResult;

    //------------------------------------------------------------------------------------------------------------------

    protected function initJob(): void
    {
        $this->lastActionNotice = null;
    }

    protected function getJobId(): string
    {
        $value = get_class($this);
        $value = explode('\\',$value);
        return array_pop($value);
    }

    //------------------------------------------------------------------------------------------------------------------

    protected function logMsg(string $msg, string $endLine = "\n"): bool
    {
        if (!$this->verbose)
            return false;

        echo $msg . $endLine;
        ob_flush();
        return true;
    }
}
