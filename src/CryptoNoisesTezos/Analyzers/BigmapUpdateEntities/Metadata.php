<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;

use CryptoNoisesTezos\Models;

class Metadata extends AbstractEntity
{
    public function __construct()
    {
        $this->dbDataModel = Models\BigmapDataMetadata::model();

        $this->postActions[] = new PostActions\ContractMetadata();
        $this->postActions[] = new PostActions\TezosProfiles();
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
        return ['value' => $cValString];
    }
}
