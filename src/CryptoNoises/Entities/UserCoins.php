<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Models;

class UserCoins
{
    public static function IsTracking($userId, $currencyInId, $currencyOutId): bool
    {
        $item = Models\UsersCurrencyTrack::model()->getItem($userId, $currencyInId, $currencyOutId);
        return $item != null;
    }

    public static function Track($userId, $currencyInId, $currencyOutId): void
    {
        Models\UsersCurrencyTrack::model()->saveItem($userId, $currencyInId, $currencyOutId);
    }

    public static function Untrack($userId, $currencyInId, $currencyOutId): void
    {
        Models\UsersCurrencyTrack::model()->dropItem($userId, $currencyInId, $currencyOutId);
    }

    public static function ToggleTracking($userId, $currencyInId, $currencyOutId): void
    {
        if (self::IsTracking($userId, $currencyInId, $currencyOutId))
            self::Untrack($userId, $currencyInId, $currencyOutId);
        else
            self::Track($userId, $currencyInId, $currencyOutId);
    }
}
