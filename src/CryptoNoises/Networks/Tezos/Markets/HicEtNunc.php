<?php

namespace CryptoNoises\Networks\Tezos\Markets;

use CryptoNoises\Entities\TokenActions;
use CryptoNoises\GatewayTezos;
use CryptoCore\Http\AbstractVendor;
use CryptoCore\Setapp;
use CryptoNoises\Networks\Tezos\Hunters\HicEtNuncHunter;

class HicEtNunc extends AbstractVendor
{
    public static function GetSwapDetails($swapId): ?TokenActions\Swap
    {
        if ($swapId === null)
            return null;

        $value = GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\HicEtNunc::Bigmap_Swaps, $swapId);

        if ($value === null)
            return null;

        $swap = new TokenActions\Swap($swapId);
        $swap->seller       = $value['issuer'];
        $swap->tokenAddress = HicEtNuncHunter::kTokensKTID;
        $swap->tokenId      = $value['objkt_id'];
        return $swap;
    }
}
