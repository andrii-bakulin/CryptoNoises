<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;

use CryptoNoisesTezos\Models;

class LedgerLight extends AbstractEntity
{
    public function __construct()
    {
        $this->dbDataModel = Models\BigmapDataLedgerLight::model();
    }

    public function primaryKey($key): array
    {
        return [
            'bigmap'            => $this->bigmap,
            'exkey_token_id'    => self::KeyToExkey($key),
        ];
    }

    public function packSavingValues(string $cValString): array
    {
        $address = json_decode($cValString, true); // strips the quotes >> "tz1..." -> tz1...
        $address = self::KeyToExkey($address);  // convert address to BIGINT

        return [
            'exkey_token_address' => self::KeyToExkey( Models\Bigmaps::model()->getContractByBigmap($this->bigmap) ),
            'value'               => $address,
        ];
    }
}
