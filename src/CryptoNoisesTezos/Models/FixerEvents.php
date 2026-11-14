<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Models\FixerEventsAbstract;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssDb;

class FixerEvents extends FixerEventsAbstract
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    protected function dbConnection(): VssDb
    {
        return Vss::dbTezos();
    }
}
