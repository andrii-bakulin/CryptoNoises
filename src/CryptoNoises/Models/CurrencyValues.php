<?php

namespace CryptoNoises\Models;

use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class CurrencyValues extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'currency_values';
    }

    public function getItemByIds($currencyInId, $currencyOutId)
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE currency_in_id=:currency_in_id AND currency_out_id=:currency_out_id";

        return Vss::db()->queryRow($sql, ['currency_in_id'=>$currencyInId, 'currency_out_id'=>$currencyOutId]);
    }

    public function save($currencyInId, $currencyOutId, $value)
    {
        $item = [
            'currency_in_id'    => $currencyInId,
            'currency_out_id'   => $currencyOutId,
            'value'             => $value,
            'ts_event'          => Time::Now(),
        ];

        return Vss::db()->insertOrUpdate($this->tableName(), $item, $item);
    }
}
