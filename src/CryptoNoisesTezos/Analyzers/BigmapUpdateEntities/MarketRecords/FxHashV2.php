<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Core\Log;

trait FxHashV2
{
    public static function PackDealRecord_FxHashV2_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['gentk']['id'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        if (($tokenAddress = Setapp\FxHash::GetTokensKTIDByVersion($value['gentk']['version'])) === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Unknown gentk-version", $value);
            return null;
        }

        $rec = new MarketDealRecord();

        $rec->exkey_token_address   = self::KeyToExkey($tokenAddress);
        $rec->exkey_token_id        = self::KeyToExkey($value['gentk']['id']);
        $rec->exkey_token_artist    = null;
        $rec->exkey_token_owner     = self::KeyToExkey($value['seller']);
        $rec->objkt_amount          = 1;
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['price']);
        $rec->objkt_royalties       = null;

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        return $rec;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function PackOfferRecord_FxHashV2(int $bigmap, /* mixed */ $value): ?MarketOfferRecord
    {
        if ($value['gentk']['id'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        if (($tokenAddress = Setapp\FxHash::GetTokensKTIDByVersion($value['gentk']['version'])) === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Unknown gentk-version", $value);
            return null;
        }

        $rec = new MarketOfferRecord();
        $rec->exkey_token_address   = self::KeyToExkey($tokenAddress);
        $rec->exkey_token_id        = self::KeyToExkey($value['gentk']['id']);
        $rec->exkey_token_buyer     = self::KeyToExkey($value['buyer']);
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['price']);

        $rec->is_active             = MarketOfferRecord::kActive_Yes;
        return $rec;
    }
}
