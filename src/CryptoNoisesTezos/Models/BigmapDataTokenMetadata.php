<?php

namespace CryptoNoisesTezos\Models;

class BigmapDataTokenMetadata extends BigmapDataAbstract
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'bigmap_data_token_metadata';
    }
}
