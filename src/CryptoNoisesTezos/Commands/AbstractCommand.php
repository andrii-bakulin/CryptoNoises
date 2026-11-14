<?php

namespace CryptoNoisesTezos\Commands;

use CryptoNoisesTezos\Models;
use CryptoCore\Models\ConfigAbstract;

abstract class AbstractCommand extends \CryptoCore\Commands\AbstractCommand
{
    protected function lockConfig(): ConfigAbstract
    {
        return Models\Config::model();
    }
}
