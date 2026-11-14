<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;

use CryptoNoisesTezos\Models;

class TokenMetadata extends AbstractEntity
{
    public function __construct()
    {
        $this->dbDataModel = Models\BigmapDataTokenMetadata::model();

        $this->postActions[] = new PostActions\TokenDetector_TokenMetadata();
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
