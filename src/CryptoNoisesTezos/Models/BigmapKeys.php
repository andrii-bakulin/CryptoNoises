<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class BigmapKeys extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'bigmap_keys';
    }

    public function insert(string $key, ?string $keyExtra): int
    {
        return Vss::dbTezos()->insertAndGetId($this->tableName(), [
            'key'       => $key,
            'key_extra' => $keyExtra,
        ]);
    }

    public function getIdByValue(string $key): ?int
    {
        return Vss::dbTezos()->selectScalar('exkey_id', $this->tableName(), ['key' => $key]);
    }

    public function getItemById(int $exkeyId): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['exkey_id' => $exkeyId]);
    }
}
