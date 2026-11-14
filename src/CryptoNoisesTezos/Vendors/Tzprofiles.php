<?php

namespace CryptoNoisesTezos\Vendors;

use CryptoCore\Http\AbstractVendor;

class Tzprofiles extends AbstractVendor
{
    public static function GetProfileDetails(string $address): ?array
    {
        // The API accepts a wallet or a contract address. Prefer the wallet address: one wallet may be linked
        // from several contracts.

        $result = self::GetDataJson("https://api.tzprofiles.com/$address");

        return $result->isSuccess() ? $result->data : null;
    }
}
