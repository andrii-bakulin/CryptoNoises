<?php

namespace CryptoNoisesTezos\Core;

use CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;
use CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\AbstractEntity;
use CryptoNoisesTezos\Models;

class Bigmap
{
    /**
     * @param array|null $bigmapItem
     * @return BigmapUpdateEntities\AbstractEntity|null
     */
    public static function FactoryEntity(?array $bigmapItem): ?BigmapUpdateEntities\AbstractEntity
    {
        if ($bigmapItem == null)
            return null;

        switch ($bigmapItem['type'])
        {
            case Models\Bigmaps::kTYPE_Basic:
                return new BigmapUpdateEntities\Basic();

            case Models\Bigmaps::kTYPE_Ledger:
                return new BigmapUpdateEntities\Ledger($bigmapItem['fieldkey_token_id'], $bigmapItem['fieldkey_address']);

            case Models\Bigmaps::kTYPE_LedgerLight:
                return new BigmapUpdateEntities\LedgerLight();

            case Models\Bigmaps::kTYPE_MarketDeals:
                return new BigmapUpdateEntities\MarketDeals();

            case Models\Bigmaps::kTYPE_MarketOffers:
                return new BigmapUpdateEntities\MarketOffers();

            case Models\Bigmaps::kTYPE_Metadata:
                return new BigmapUpdateEntities\Metadata();

            case Models\Bigmaps::kTYPE_Operators:
                return new BigmapUpdateEntities\Operator($bigmapItem['fieldkey_token_id'], $bigmapItem['fieldkey_address'], $bigmapItem['fieldkey_address2']);

            case Models\Bigmaps::kTYPE_Supply:
                return new BigmapUpdateEntities\Supply();

            case Models\Bigmaps::kTYPE_TokenMetadata:
                return new BigmapUpdateEntities\TokenMetadata();
        }

        return null;
    }

    /**
     * @param int $bigmap
     * @param int $exkey
     * @return mixed|null
     */
    public static function GetValueByBigmapAndExkey(int $bigmap, int $exkey)
    {
        return self::GetValueByBigmapAndKey($bigmap, AbstractEntity::ExkeyToKey($exkey));
    }

    /**
     * @param int $bigmap
     * @param string|null $key
     * @return mixed|null
     */
    public static function GetValueByBigmapAndKey(int $bigmap, ?string $key)
    {
        $bigmapItem = Models\Bigmaps::model()->getItem($bigmap);

        if ($bigmapItem === null)
            return null;

        $entity = self::FactoryEntity($bigmapItem);

        if ($entity === null)
            return null;

        $entity->init($bigmap, 0);

        $item = $entity->model()->getItemByPk( $entity->primaryKey($key) );

        return $item ? json_decode($item['value'], true) : null;
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $keyValue
     */
    public static function KeyToExkey($keyValue): int
    {
        return BigmapKeyCodec::KeyToExkey($keyValue);
    }

    /**
     * @return mixed|null
     */
    public static function ExkeyToKey(int $exkey)
    {
        return BigmapKeyCodec::ExkeyToKey($exkey);
    }
}