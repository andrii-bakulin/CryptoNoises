<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class CurrencyOut extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'currency_out';
    }

    public function getItemById($currencyOutId)
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE currency_out_id=:currency_out_id";

        return Vss::db()->queryRow($sql, ['currency_out_id'=>$currencyOutId]);
    }

    public function selectPublicFull(?string $currencyOutIdIgnore=null)
    {
        if ($currencyOutIdIgnore)
        {
            $sql = "
                SELECT *
                FROM {$this->tableName()}
                WHERE is_enabled > 0
                  AND currency_out_id <> :currency_out_id
                ORDER BY order_index DESC, name, currency_out_id
            ";
            return Vss::db()->query($sql, ['currency_out_id' => $currencyOutIdIgnore]);
        }

        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE is_enabled > 0
            ORDER BY order_index DESC, name, currency_out_id
        ";
        return Vss::db()->query($sql);
    }
}
