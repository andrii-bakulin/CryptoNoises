<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;

use CryptoNoisesTezos\Models;

class Operator extends AbstractEntity
{
    protected string $keyNameTokenId;
    protected string $keyNameOperator;
    protected string $keyNameOwner;

    public function __construct(string $keyNameTokenId, string $keyNameOperator, string $keyNameOwner)
    {
        $this->keyNameTokenId  = $keyNameTokenId;
        $this->keyNameOperator = $keyNameOperator;
        $this->keyNameOwner    = $keyNameOwner;

        $this->dbDataModel = Models\BigmapDataOperators::model();
    }

    public function primaryKey($key): array
    {
        return [
            'bigmap'            => $this->bigmap,
            'exkey_token_id'    => self::KeyToExkey($key[$this->keyNameTokenId]),
            'exkey_operator'    => self::KeyToExkey($key[$this->keyNameOperator]),
            'exkey_owner'       => self::KeyToExkey($key[$this->keyNameOwner]),
        ];
    }

    public function packSavingValues(string $cValString): array
    {
        return ['value' => $cValString];
    }
}
