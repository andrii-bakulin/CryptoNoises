<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class UsersCurrencyTrack extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'users_currency_track';
    }

    public function getItem($userId, $currencyInId, $currencyOutId)
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE user_id=:user_id AND currency_in_id=:currency_in_id AND currency_out_id=:currency_out_id";

        $item = [
            'user_id'           => $userId,
            'currency_in_id'    => $currencyInId,
            'currency_out_id'   => $currencyOutId,
        ];

        return Vss::db()->query($sql, $item);
    }

    public function saveItem($userId, $currencyInId, $currencyOutId)
    {
        $item = [
            'user_id'           => $userId,
            'currency_in_id'    => $currencyInId,
            'currency_out_id'   => $currencyOutId,
        ];

        return Vss::db()->insert($this->tableName(), $item, 'IGNORE');
    }

    public function dropItem($userId, $currencyInId, $currencyOutId)
    {
        $item = [
            'user_id'           => $userId,
            'currency_in_id'    => $currencyInId,
            'currency_out_id'   => $currencyOutId,
        ];

        return Vss::db()->delete($this->tableName(), $item);
    }

    public function selectAll($userId)
    {
        $sql = "
            SELECT uc.* 
              FROM {$this->tableName()} AS uc
              JOIN ".CurrencyIn::model()->tableName()." AS co
                ON uc.currency_in_id = co.currency_in_id
              JOIN ".CurrencyOut::model()->tableName()." AS cc
                ON uc.currency_out_id = cc.currency_out_id
             WHERE uc.user_id = :user_id
               AND co.is_enabled > 0 AND co.is_public > 0
               AND cc.is_enabled > 0
             ORDER BY co.order_index DESC, co.name, co.currency_in_id,
                      cc.order_index DESC, cc.name, cc.currency_out_id
        ";

        return Vss::db()->query($sql, ['user_id'=>$userId]);
    }
}
