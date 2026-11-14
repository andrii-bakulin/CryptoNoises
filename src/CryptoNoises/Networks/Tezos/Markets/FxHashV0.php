<?php

namespace CryptoNoises\Networks\Tezos\Markets;

use CryptoCore\Setapp;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\GatewayTezos;
use CryptoCore\Http\AbstractVendor;

class FxHashV0 extends AbstractVendor
{
    public static function GetSwapDetails($swapId): ?TokenActions\Swap
    {
        if ($swapId === null)
            return null;

        $value = GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\FxHash_v0::Bigmap_Swaps, $swapId);

        if ($value === null)
            return null;

        $swap = new TokenActions\Swap($swapId);
        $swap->seller       = $value['issuer'];
        $swap->tokenAddress = Setapp\FxHash_v0::TokensKTID;
        $swap->tokenId      = $value['objkt_id'];
        return $swap;
    }
}
