<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoNoisesTezos\Core\Log;
use CryptoCore\Time;

trait ObjktComV3
{
    public static function PackDealRecord_ObjktComV3_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['token']['address'] === null || $value['token']['token_id'] === null || $value['creator'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['token']['address']);
        $rec->exkey_token_id        = self::KeyToExkey($value['token']['token_id']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['creator']);
        $rec->objkt_amount          = $value['editions'];
        $rec->objkt_price_xtz       = isset($value['currency']['tez']) ? self::NormalizeXtzPrice($value['amount']) : null;

        self::PackDealRecord_ObjktComV3_PackShares($rec, $value);

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        return $rec;
    }

    public static function PackDealRecord_ObjktComV3_AuctionsEnglish(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['token']['address'] === null || $value['token']['token_id'] === null || $value['creator'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['token']['address']);
        $rec->exkey_token_id        = self::KeyToExkey($value['token']['token_id']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['creator']);
        $rec->objkt_amount          = 1;
        $rec->objkt_price_xtz       = null;
        $rec->ts_deal_expired       = Time::NormalizeTimestamp($value['end_time']);

        self::PackDealRecord_ObjktComV3_PackShares($rec, $value);

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        $rec->ts_active_until       = $rec->ts_deal_expired;
        return $rec;
    }

    public static function PackDealRecord_ObjktComV3_AuctionsDutch(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['token']['address'] === null || $value['token']['token_id'] === null || $value['creator'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['token']['address']);
        $rec->exkey_token_id        = self::KeyToExkey($value['token']['token_id']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['creator']);
        $rec->objkt_amount          = $value['editions'];
        $rec->objkt_price_xtz       = null;
        $rec->ts_deal_expired       = Time::NormalizeTimestamp($value['end_time']);

        self::PackDealRecord_ObjktComV3_PackShares($rec, $value);

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        $rec->ts_active_until       = $rec->ts_deal_expired;
        return $rec;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function PackOfferRecord_ObjktComV3(int $bigmap, /* mixed */ $value): ?MarketOfferRecord
    {
        if ($value['token']['address'] === null)
        {
            Log::Error("MarketRecords", "PackOfferRecord_ObjktComV3", "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketOfferRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['token']['address']);

        // Same handling as v2 (not re-verified for v3)
        if ($value['token']['token_id'] !== null)
        {
            $rec->exkey_token_id    = self::KeyToExkey($value['token']['token_id']);
            $rec->is_active         = MarketOfferRecord::kActive_Yes;
        }
        else // Sometimes 'address exists' but token.token_id IS NULL
        {
            $rec->exkey_token_id    = null;
            $rec->is_active         = MarketOfferRecord::kActive_No;
        }

        $rec->exkey_token_buyer     = self::KeyToExkey($value['creator']);
        $rec->objkt_price_xtz       = isset($value['currency']['tez']) ? self::NormalizeXtzPrice($value['amount']) : null;

        return $rec;
    }

    //------------------------------------------------------------------------------------------------------------------

    /*
        "shares":{
            "<address>":"250",
            "<address>":"750"
        },
     */
    private static function PackDealRecord_ObjktComV3_PackShares(MarketDealRecord $rec, /* mixed */ $value)
    {
        if (count($value['shares']) == 0)
            return;

        arsort($value['shares'], SORT_NUMERIC);

        $address = array_key_first($value['shares']);

        $rec->exkey_token_artist    = self::KeyToExkey($address);
        $rec->objkt_royalties       = $value['shares'][$address];
    }
}
