<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Models;
class Address
{
    public static function Normalize(?string $address): string
    {
        $address = trim((string) $address);

        // Accept an objkt.com profile URL (https://objkt.com/@alias) as alias.tez
        if (preg_match('#^https://objkt\.com/@(.+)#iu', $address, $m) > 0)
            return $m[1].'.tez';

        if (preg_match('/(tz[1-9][a-z0-9]{33})/iu', $address, $m) > 0)
        {
            $address = $m[1];

            // Convert { 'Tz...' | 'TZ...' } --> 'tz...'
            $address = 'tz'.substr($address, 2);
        }

        return $address;
    }

    public static function GetOrCreateAdid(?string $address): ?int
    {
        $item = self::GetItemByAddress($address, true);

        return $item ? $item['adid'] : null;
    }

    public static function GetAdid(?string $address): ?int
    {
        $item = self::GetItemByAddress($address, false);

        return $item ? $item['adid'] : null;
    }

    private static function GetItemByAddress(?string $address, bool $isRequireAutoCreate): ?array
    {
        if ($address === null)
            return null;

        $item = Models\Address::model()->getItemByAddress($address);

        if (!empty($item))
            return $item;

        if (!$isRequireAutoCreate)
            return null;

        Models\Address::model()->create($address);

        return Models\Address::model()->getItemByAddress($address);
    }
}
