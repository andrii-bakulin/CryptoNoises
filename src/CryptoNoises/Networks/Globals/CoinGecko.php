<?php

namespace CryptoNoises\Networks\Globals;

use CryptoCore\Http\AbstractVendor;

class CoinGecko extends AbstractVendor
{
    public static function GetCoinDetails(string $coinGeckoId): ?array
    {
        $syncResult = self::GetDataJson("https://api.coingecko.com/api/v3/coins/$coinGeckoId");

        if ($syncResult->notSuccess())
            return null;

        return $syncResult->data;
    }
}
