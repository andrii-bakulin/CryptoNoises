<?php

namespace CryptoNoisesTezos\Models;

class FixerBigmapUpdatesAnalyze extends FixerAbstract
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'fixer_bigmap_updates_analyze';
    }
}
