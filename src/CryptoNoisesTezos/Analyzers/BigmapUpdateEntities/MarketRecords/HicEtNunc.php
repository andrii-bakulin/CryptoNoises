<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoNoisesTezos\Core\Log;

trait HicEtNunc
{
    public static function PackDealRecord_HicEtNunc_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['objkt_id'] === null || $value['issuer'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey('KT1RJ6PbjHpwc3M5rw5s2Nbmefwbuwbdxton');
        $rec->exkey_token_id        = self::KeyToExkey($value['objkt_id']);
        $rec->exkey_token_artist    = self::KeyToExkey($value['creator']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['issuer']);
        $rec->objkt_amount          = $value['objkt_amount'];
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['xtz_per_objkt']);
        $rec->objkt_royalties       = $value['royalties'];

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        return $rec;
    }
}
