<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;

use CryptoNoisesTezos\Models;

class Supply extends AbstractEntity
{
    public function __construct()
    {
        $this->dbDataModel = Models\BigmapDataSupply::model();
    }

    public function primaryKey($key): array
    {
        return [
            'bigmap'    => $this->bigmap,
            'exkey'     => self::KeyToExkey($key),
        ];
    }

    public function packSavingValues(string $cValString): array
    {
        return [
            'value'     => $cValString,
            'value_num' => json_decode($cValString),
        ];
    }
}
