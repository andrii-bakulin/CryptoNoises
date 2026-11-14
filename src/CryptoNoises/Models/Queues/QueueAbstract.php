<?php

namespace CryptoNoises\Models\Queues;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssDb;

abstract class QueueAbstract extends \CryptoCore\Models\Queues\QueueAbstract
{
    protected function dbConnection(): VssDb
    {
        return Vss::db();
    }
}
