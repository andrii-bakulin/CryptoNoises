<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

use CryptoNoisesTezos\Core\Log;

trait Kalamint
{
    public static function PackDealRecord_Kalamint_Swaps(int $bigmap, /* mixed */ $value): ?MarketDealRecord
    {
        if ($value['token_id'] === null)
        {
            Log::Error("MarketRecords", __FUNCTION__, "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketDealRecord();
        $rec->exkey_token_address   = self::KeyToExkey('KT1EpGgjQs73QfFJs9z7m1Mxm5MTnpC2tqse');
        $rec->exkey_token_id        = self::KeyToExkey($value['token_id']);
        $rec->exkey_token_artist    = self::KeyToExkey($value['creator']);
        $rec->exkey_token_owner     = self::KeyToExkey($value['owner']);
        $rec->objkt_amount          = 1;
        $rec->objkt_price_xtz       = self::NormalizeXtzPrice($value['price']);
        $rec->objkt_royalties       = $value['creator_royalty'];

        $rec->is_active             = $value['on_sale'] ? MarketDealRecord::kActive_Yes : MarketDealRecord::kActive_No;
        return $rec;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function PackOfferRecord_Kalamint(int $bigmap, /* mixed */ $value): ?MarketOfferRecord
    {
        if ($value['nft_contract'] === null || $value['nft_token_id'] === null)
        {
            Log::Error("MarketRecords", "PackOfferRecord_Kalamint", "Bad params at #$bigmap", $value);
            return null;
        }

        $rec = new MarketOfferRecord();
        $rec->exkey_token_address   = self::KeyToExkey($value['nft_contract']);
        $rec->exkey_token_id        = self::KeyToExkey($value['nft_token_id']);
        $rec->exkey_token_buyer     = self::KeyToExkey($value['issuer']);

        switch ($value['offered_token_contract'])
        {
            case 'KT1VVNTAxmYjSbxvduQu4k4V2AeNn4y4EzZK': # XTZ (Kalamint Art House | Offers catcher)
                $rec->objkt_price_xtz = self::NormalizeXtzPrice($value['offer_amount']);
                break;

            case 'KT1A5P4ejnLix13jtadsfV9GCnXLMNnab8UT': # KALAM
            case 'KT1GRSvLoikDsXujKgZPsGLX8k8VvR2Tq95b': # PLENTY
            case 'KT1BHCumksALJQJ8q8to2EPigPW6qpyTr7Ng': # CRUNCH
            case 'KT1K9gCRgaLRFKTErYt1wVxA3Frb9FjasjTV': # kUSD
            default:
                // non-XTZ offers are not stored
                break;
        }

        $rec->is_active             = MarketOfferRecord::kActive_Yes;
        return $rec;
    }
}
