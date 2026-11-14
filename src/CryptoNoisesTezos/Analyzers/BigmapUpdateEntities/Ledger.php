<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;

use CryptoNoisesTezos\Models;

class Ledger extends AbstractEntity
{
    protected string $keyNameTokenId;
    protected string $keyNameAddress;

    public function __construct(string $keyNameTokenId, string $keyNameAddress)
    {
        $this->keyNameTokenId = $keyNameTokenId;
        $this->keyNameAddress = $keyNameAddress;

        $this->dbDataModel = Models\BigmapDataLedger::model();
    }

    public function primaryKey($key): array
    {
        return [
            'bigmap'            => $this->bigmap,
            'exkey_token_id'    => self::KeyToExkey($key[$this->keyNameTokenId]),
            'exkey_address'     => self::KeyToExkey($key[$this->keyNameAddress]),
        ];
    }

    public function packSavingValues(string $cValString): array
    {
        $valueNum = json_decode($cValString);

        if (!is_numeric($valueNum) || preg_match('/^(-?)([0-9]+)$/', $valueNum) == false)
            $valueNum = null;

        return [
            'exkey_token_address' => self::KeyToExkey( Models\Bigmaps::model()->getContractByBigmap($this->bigmap) ),
            'value'               => $cValString,
            'value_num'           => $valueNum,
        ];
    }
}
