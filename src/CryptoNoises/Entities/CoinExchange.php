<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Core\Log;
use CryptoNoises\Models;

class CoinExchange
{
    protected int $userId;
    protected string $currency;

    private function __construct($userId, $currency)
    {
        $this->userId = $userId;
        $this->currency = $currency;
    }

    public static function Create($userId, $currency): CoinExchange
    {
        return new CoinExchange($userId, $currency);
    }

    public static function CreateByWallet(UserWallet $wallet): CoinExchange
    {
        return self::Create($wallet->getUserId(), $wallet->getCurrency());
    }

    /**
     * @return Coin[]
     */
    protected function convertToValues($amount): array
    {
        $currencyInId = $this->currency;
        $results = [];

        if ($currencyInId == '')
            return $results;

        $currencyOutIds = Models\UsersCurrencyConvert::model()->selectIds($this->userId);

        $coinItem = Models\CurrencyIn::model()->getItem($currencyInId);

        foreach ($currencyOutIds as $currencyOutId)
        {
            $coinsValue = Models\CurrencyValues::model()->getItemByIds($currencyInId, $currencyOutId);

            if ($coinsValue == null)
            {
                Log::Warning(get_class($this), __FUNCTION__, "Failed to get CoinsValue for pair [$currencyInId]-[$currencyOutId]", [
                    'userId' => $this->userId,
                    'currencyOutIds' => $currencyOutIds,
                ]);
                continue;
            }

            $newAmount = $amount * $coinsValue['value'] * $coinItem['exchange_normalize_mult'] / $coinItem['exchange_normalize_div'];

            $results[$currencyOutId] = new Coin($newAmount, $currencyOutId);
        }

        return $results;
    }

    public function convertToString($amount): string
    {
        if ($amount == 0)
            return '';

        $converts = $this->convertToValues($amount);

        if (empty($converts))
            return '';

        $results = [];

        foreach ($converts as $coin)
        {
            $results[] = $coin->getFriendlyValue();
        }

        return '('.join(' / ',$results).')';
    }
}
