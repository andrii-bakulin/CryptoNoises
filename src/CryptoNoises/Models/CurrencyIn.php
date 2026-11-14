<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class CurrencyIn extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'currency_in';
    }

    public function selectEnabled()
    {
        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE is_enabled > 0
            ORDER BY order_index DESC, name, currency_in_id
        ";

        return Vss::db()->query($sql);
    }

    public function selectPublic()
    {
        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE is_enabled > 0 AND is_public > 0
            ORDER BY order_index DESC, name, currency_in_id
        ";

        return Vss::db()->query($sql);
    }

    public function getItem($currencyInId)
    {
        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE is_enabled > 0 AND is_public > 0
              AND currency_in_id = :currency_in_id
        ";

        return Vss::db()->queryRow($sql, ['currency_in_id'=>$currencyInId]);
    }
}
