<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoNoisesTezos\Core\Log;
use CryptoCore\Time;

trait Versum
{
    public static function PackDealRecord_Versum_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['token']['address'] === null || $value['token']['nat'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['token']['address']);
        $rec->exkey_token_id        = self::KeyToExkey($value['token']['nat']);
        $rec->exkey_token_artist    = null;
        $rec->exkey_token_owner     = self::KeyToExkey($value['seller']);
        $rec->objkt_amount          = $value['token_left_amount'];
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['starting_price_in_nat']);
        $rec->objkt_royalties       = null;

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        return $rec;
    }

    public static function PackDealRecord_Versum_Auctions(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['token']['address'] === null || $value['token']['nat'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['token']['address']);
        $rec->exkey_token_id        = self::KeyToExkey($value['token']['nat']);
        $rec->exkey_token_artist    = null;
        $rec->exkey_token_owner     = self::KeyToExkey($value['seller']);
        $rec->objkt_amount          = $value['token_amount'];
        $rec->objkt_price_xtz       = null;
        $rec->objkt_royalties       = null;
        $rec->ts_deal_expired       = Time::NormalizeTimestamp($value['end_timestamp']);

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        $rec->ts_active_until       = $rec->ts_deal_expired;
        return $rec;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function PackOfferRecord_Versum(int $bigmap, /* mixed */ $value): ?MarketOfferRecord
    {
        if ($value['token']['address'] === null || $value['token']['nat'] === null)
        {
            Log::Error("MarketRecords", "PackOfferRecord_Versum", "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketOfferRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['token']['address']);
        $rec->exkey_token_id        = self::KeyToExkey($value['token']['nat']);
        $rec->exkey_token_buyer     = self::KeyToExkey($value['buyer']);
        $rec->objkt_price_xtz       = $value['currency'] === null ? self::NormalizeXtzPrice($value['price_in_nat']) : null;

        $rec->is_active             = MarketOfferRecord::kActive_Yes;
        return $rec;
    }
}
