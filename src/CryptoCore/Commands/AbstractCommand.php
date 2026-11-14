<?php

namespace CryptoCore\Commands;

use CryptoCore\Models\ConfigAbstract;
use CryptoCore\Vss\VssCommand;

abstract class AbstractCommand extends VssCommand
{
    /**
     * The config model that holds the command locks of this app.
     */
    abstract protected function lockConfig(): ConfigAbstract;

    protected function checkLockAndWaitUntilNoOtherInstanceRuns(?string $lockerKey, int $waitTimeout): bool
    {
        if ($lockerKey && $this->lockConfig()->getValue($lockerKey) && empty($this->getCmdVar('unlock')))
        {
            $this->logMsg("Command locked!");
            return false;
        }

        return $this->waitUntilNoOtherInstanceRuns($waitTimeout);
    }
}
