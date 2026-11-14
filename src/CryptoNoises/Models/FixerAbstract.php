<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssDb;

abstract class FixerAbstract extends \CryptoCore\Models\FixerAbstract
{
    protected function dbConnection(): VssDb
    {
        return Vss::db();
    }
}
