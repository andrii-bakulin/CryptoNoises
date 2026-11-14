<?php

namespace CryptoNoisesTezos\Models\Log;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Errors extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'log_errors';
    }

    public function save($item): int
    {
        if (is_object($item['data']) || is_array($item['data']))
            $item['data'] = json_encode($item['data']);

        return (int) Vss::dbTezosLog()->insertAndGetId($this->tableName(), $item);
    }
}
