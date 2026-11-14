<?php

namespace CryptoNoisesTezos\Models;

use CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords\MarketOfferRecord;

class BigmapDataMarketOffers extends BigmapDataAbstract
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'bigmap_data_market_offers';
    }

    public function removeByPk(array $primaryKey, int $level, array $saveData = []): void
    {
        parent::removeByPk($primaryKey, $level, [
            'is_active' => MarketOfferRecord::kActive_No,
        ]);
    }
}
