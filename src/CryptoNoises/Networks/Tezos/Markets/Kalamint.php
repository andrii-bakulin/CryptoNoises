<?php

namespace CryptoNoises\Networks\Tezos\Markets;

use CryptoNoises\GatewayTezos;
use CryptoCore\Http\AbstractVendor;
use CryptoCore\Setapp;
use CryptoNoises\Networks\Tezos\Hunters\KalamintHunter;

class Kalamint extends AbstractVendor
{
    public static function GetTokenDetails(string $tokenAddress, string $tokenId): ?array
    {
        if ($tokenAddress != KalamintHunter::kTokensKTID)
            return null;

        return GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\Kalamint::Bigmap_Tokens, $tokenId);
    }
}
