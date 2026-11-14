<?php

namespace CryptoNoises\Networks\Tezos\Markets;

use CryptoCore\Setapp;
use CryptoNoises\Core\Log;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\GatewayTezos;
use CryptoCore\Http\AbstractVendor;

class FxHashV2 extends AbstractVendor
{
    public static function GetSwapDetails($swapId): ?TokenActions\Swap
    {
        if ($swapId === null)
            return null;

        $value = GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\FxHash_v2::Bigmap_Swaps, $swapId);

        if ($value === null)
            return null;

        $swap = new TokenActions\Swap($swapId);
        $swap->tokenAddress = Setapp\FxHash::GetTokensKTIDByVersion($value['gentk']['version']);

        if ($swap->tokenAddress === null)
        {
            Log::Error("FxHash", "GetSwapDetails", "Unknown gentk-version swapId=$swapId", $value);
            return null;
        }

        $swap->tokenId      = $value['gentk']['id'];
        $swap->seller       = $value['seller'];
        return $swap;
    }
}
