<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoNoisesTezos\Core\Log;
use CryptoCore\Time;

/**
 * Notice (1):
 *      state=0=ACTIVE (or) EXPIRED         (token still on Auction Contract)
 *      state=1=CANCELED                    (token returned to owner)
 *      state=2=AUCTION-SUCCESS-COMPLETED   (token transferred to winner)
 *
 * Notice (2): Why ts_active_until stays null:
 *      An expired auction that the owner did not cancel keeps the token on the auction contract,
 *      so the deal stays active until the owner takes the token back (state=1).
 */
trait ObjktComV1
{
    public static function PackDealRecord_ObjktComV1_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['fa2'] === null || $value['objkt_id'] === null || $value['issuer'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['fa2']);
        $rec->exkey_token_id        = self::KeyToExkey($value['objkt_id']);
        $rec->exkey_token_artist    = self::KeyToExkey($value['artist']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['issuer']);
        $rec->objkt_amount          = $value['amount'];
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['xtz_per_objkt']);
        $rec->objkt_royalties       = $value['royalties'];

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        return $rec;
    }

    public static function PackDealRecord_ObjktComV1_AuctionsEnglish(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['fa2'] === null || $value['objkt_id'] === null || $value['creator'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['fa2']);
        $rec->exkey_token_id        = self::KeyToExkey($value['objkt_id']);
        $rec->exkey_token_artist    = self::KeyToExkey($value['artist']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['creator']);
        $rec->objkt_amount          = 1;
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['current_price']);
        $rec->objkt_royalties       = $value['royalties'];
        $rec->ts_deal_expired       = Time::NormalizeTimestamp($value['end_time']);

        $rec->is_active             = $value['state'] == '0' ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        $rec->ts_active_until       = null; // see Notice (2) in the trait docblock
        return $rec;
    }

    public static function PackDealRecord_ObjktComV1_AuctionsDutch(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['fa2'] === null || $value['objkt_id'] === null || $value['creator'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['fa2']);
        $rec->exkey_token_id        = self::KeyToExkey($value['objkt_id']);
        $rec->exkey_token_artist    = self::KeyToExkey($value['artist']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['creator']);
        $rec->objkt_amount          = 1;
        $rec->objkt_price_xtz       = null;
        $rec->objkt_royalties       = $value['royalties'];
        $rec->ts_deal_expired       = Time::NormalizeTimestamp($value['end_time']);

        $rec->is_active             = $value['state'] == '0' ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        $rec->ts_active_until       = null; // see Notice (2) in the trait docblock
        return $rec;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function PackOfferRecord_ObjktComV1(int $bigmap, /* mixed */ $value): ?MarketOfferRecord
    {
        if ($value['fa2'] === null || $value['objkt_id'] === null)
        {
            Log::Error("MarketRecords", "PackOfferRecord_ObjktComV1", "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketOfferRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['fa2']);
        $rec->exkey_token_id        = self::KeyToExkey($value['objkt_id']);
        $rec->exkey_token_buyer     = self::KeyToExkey($value['issuer']);
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['xtz_per_objkt']);

        $rec->is_active             = MarketOfferRecord::kActive_Yes;
        return $rec;
    }
}
