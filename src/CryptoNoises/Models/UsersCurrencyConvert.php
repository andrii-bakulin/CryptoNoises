<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class UsersCurrencyConvert extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'users_currency_convert';
    }

    public function bindCurrency(int $userId, string $currencyOutId): void
    {
        Vss::db()->insert($this->tableName(), [
            'user_id'           => $userId,
            'currency_out_id'   => $currencyOutId,
        ], 'IGNORE');
    }

    public function unbindCurrency(int $userId, string $currencyOutId): void
    {
        Vss::db()->delete($this->tableName(), [
            'user_id'           => $userId,
            'currency_out_id'   => $currencyOutId,
        ]);
    }

    public function isCurrencyBound(int $userId, string $currencyOutId): bool
    {
        $sql = "
            SELECT COUNT(*)
              FROM {$this->tableName()}
             WHERE user_id = :user_id
               AND currency_out_id = :currency_out_id
        ";

        return Vss::db()->queryScalar($sql, [
            'user_id'           => $userId,
            'currency_out_id'   => $currencyOutId,
        ]) != 0;
    }

    public function toggleCurrency(int $userId, string $currencyOutId): void
    {
        if ($this->isCurrencyBound($userId, $currencyOutId))
            $this->unbindCurrency($userId, $currencyOutId);
        else
            $this->bindCurrency($userId, $currencyOutId);
    }

    public function selectIds(int $userId): array
    {
        $sql = "
            SELECT uc.currency_out_id
              FROM {$this->tableName()} AS uc
              JOIN " . CurrencyOut::model()->tableName() . " AS cc
                ON uc.currency_out_id = cc.currency_out_id
             WHERE uc.user_id = :user_id
               AND cc.is_enabled > 0
             ORDER BY cc.order_index DESC, cc.name, cc.currency_out_id
        ";

        return Vss::db()->queryColumn($sql, ['user_id' => $userId]);
    }
}
