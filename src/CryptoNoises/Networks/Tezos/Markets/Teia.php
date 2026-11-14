<?php

namespace CryptoNoises\Networks\Tezos\Markets;

use CryptoNoises\Entities\TokenActions;
use CryptoNoises\GatewayTezos;
use CryptoCore\Http\AbstractVendor;
use CryptoCore\Setapp;

class Teia extends AbstractVendor
{
    public static function GetSwapDetails($swapId): ?TokenActions\Swap
    {
        if ($swapId === null)
            return null;

        $value = GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\Teia::Bigmap_Swaps, $swapId);

        if ($value === null)
            return null;

        $swap = new TokenActions\Swap($swapId);
        $swap->seller       = $value['issuer'];
        $swap->tokenAddress = $value['fa2'];
        $swap->tokenId      = $value['objkt_id'];
        return $swap;
    }
}
