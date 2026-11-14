<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Core\Log;

trait FxHashV0
{
    public static function PackDealRecord_FxHashV0_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['objkt_id'] === null)
        {
            Log::Error("MarketRecords", "PackDealRecord_FxHashV0_Swaps", "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey(Setapp\FxHash_v0::TokensKTID);
        $rec->exkey_token_id        = self::KeyToExkey($value['objkt_id']);
        $rec->exkey_token_artist    = self::KeyToExkey($value['creator']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['issuer']);
        $rec->objkt_amount          = $value['objkt_amount'];
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['price']);
        $rec->objkt_royalties       = $value['royalties'];

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        return $rec;
    }
}
