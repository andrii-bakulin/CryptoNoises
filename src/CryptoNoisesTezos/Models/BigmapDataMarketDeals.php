<?php

namespace CryptoNoisesTezos\Models;

use CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords\MarketDealRecord;

class BigmapDataMarketDeals extends BigmapDataAbstract
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'bigmap_data_market_deals';
    }

    public function removeByPk(array $primaryKey, int $level, array $saveData = []): void
    {
        parent::removeByPk($primaryKey, $level, [
            'is_active' => MarketDealRecord::kActive_No,
        ]);
    }
}
