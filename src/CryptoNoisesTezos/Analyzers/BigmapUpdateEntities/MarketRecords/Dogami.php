<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoNoisesTezos\Core\Log;
use CryptoCore\Time;

trait Dogami
{
    public static function PackDealRecord_Dogami_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['token_id'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey('KT1NVvPsNDChrLRH5K2cy6Sc9r1uuUwdiZQd');
        $rec->exkey_token_id        = self::KeyToExkey($value['token_id']);
        $rec->exkey_token_artist    = null;
        $rec->exkey_token_owner     = self::KeyToExkey($value['owner']);
        $rec->objkt_amount          = 1;
        $rec->objkt_price_xtz       = $value['ft_symbol'] == 'XTZ' ? self::NormalizeXtzPrice($value['token_price']) : null;
        $rec->objkt_royalties       = null;
        $rec->ts_deal_expired       = Time::NormalizeTimestamp($value['end_time']);

        $rec->is_active             = $rec->objkt_amount > 0 ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        $rec->ts_active_until       = $rec->ts_deal_expired;
        return $rec;
    }
}
