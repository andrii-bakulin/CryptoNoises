<?php

namespace CryptoNoises\Networks\Tezos\Markets;

use CryptoNoises\Entities\TokenActions;
use CryptoNoises\GatewayTezos;
use CryptoCore\Http\AbstractVendor;
use CryptoCore\Setapp;

class AkaSwap extends AbstractVendor
{
    public static function GetAuctionDetails(?int $auctionId): ?TokenActions\Auction
    {
        if ($auctionId === null)
            return null;

        $value = GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\AkaSwap::Bigmap_Auctions, $auctionId);

        if ($value === null)
            return null;

        $auction = new TokenActions\Auction();
        $auction->tokenAddress  = $value['token_fa2'];
        $auction->tokenId       = $value['token_id'];
        $auction->creator       = $value['issuer'];
        $auction->highestBid    = $value['current_bid_price'];
        return $auction;
    }
}
