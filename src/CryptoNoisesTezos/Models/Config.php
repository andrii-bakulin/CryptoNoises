<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Models\ConfigAbstract;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssDb;

class Config extends ConfigAbstract
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
