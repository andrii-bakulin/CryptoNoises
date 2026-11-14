<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;

use CryptoNoisesTezos\Models;

class Basic extends AbstractEntity
{
    public function __construct()
    {
        $this->dbDataModel = Models\BigmapDataBasic::model();

        $this->postActions[] = new PostActions\TezosDomain();
        $this->postActions[] = new PostActions\TokenDetector_Basic();
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
