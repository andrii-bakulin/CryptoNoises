<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoNoisesTezos\Core\Log;

trait EightBidou
{
    public static function PackDealRecord_EightBidou_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['nft_contract_address'] === null || $value['nft_id'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['nft_contract_address']);
        $rec->exkey_token_id        = self::KeyToExkey($value['nft_id']);
        $rec->exkey_token_artist    = self::KeyToExkey($value['creator']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['seller']);
        $rec->objkt_amount          = $value['nft_amount'];
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['payment']);
        $rec->objkt_royalties       = $value['royalties'];

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        return $rec;
    }
}
