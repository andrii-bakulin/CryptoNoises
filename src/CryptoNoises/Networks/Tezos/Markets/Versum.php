<?php

namespace CryptoNoises\Networks\Tezos\Markets;

use CryptoCore\Setapp;
use CryptoNoises\Core\Log;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\GatewayTezos;
use CryptoCore\Http\AbstractVendor;

class Versum extends AbstractVendor
{
    public static function GetAuctionDetails($auctionId): ?TokenActions\Auction
    {
        if ($auctionId === null)
            return null;

        $value = GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\Versum::Bigmap_Auctions, $auctionId);

        if ($value === null)
            return null;

        $auction = new TokenActions\Auction();
        $auction->tokenAddress  = $value['token']['address'];
        $auction->tokenId       = $value['token']['nat'];
        $auction->creator       = $value['seller'];
        $auction->highestBid    = $value['bid_amount'];
        return $auction;
    }

    public static function GetOfferDetails($offerId): ?TokenActions\Offer
    {
        if ($offerId === null)
            return null;

        $value = GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\Versum::Bigmap_Offers, $offerId);

        if ($value === null)
            return null;

        if ($value['currency'] !== null)
        {
            Log::Warning('Market\Versum', 'getOfferDetails', "Unsupported currency for offer #$offerId");
            return null;
        }

        $offer = new TokenActions\Offer();
        $offer->tokenAddress    = $value['token']['address'];
        $offer->tokenId         = $value['token']['nat'];
        $offer->tokenAmount     = $value['token_amount'];
        $offer->creator         = null;
        $offer->buyer           = $value['buyer'];
        $offer->price           = $value['price_in_nat'];
        return $offer;
    }

    public static function GetSwapDetails($swapId): ?TokenActions\Swap
    {
        if ($swapId === null)
            return null;

        $value = GatewayTezos\BigmapGate::GetValueByBigmapAndKey(Setapp\Versum::Bigmap_Swaps, $swapId);

        if ($value === null)
            return null;

        if ($value['currency'] !== null)
        {
            Log::Warning('Market\Versum', 'getSwapDetails', "Unsupported currency for swap #$swapId");
            return null;
        }

        $swap = new TokenActions\Swap($swapId);
        $swap->seller       = $value['seller'];
        $swap->tokenAddress = $value['token']['address'];
        $swap->tokenId      = $value['token']['nat'];
        return $swap;
    }
}
