<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class BigmapKeysAddress extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'bigmap_keys_address';
    }

    public function insert(string $address): int
    {
        Vss::dbTezos()->insert($this->tableName(), ['address' => $address], 'IGNORE');

        return $this->getIdByValue($address);
    }

    public function getIdByValue(string $address): ?int
    {
        return Vss::dbTezos()->selectScalar('exkey_id', $this->tableName(), ['address' => $address]);
    }

    public function getValueById(int $exkeyId): ?string
    {
        return Vss::dbTezos()->selectScalar('address', $this->tableName(), ['exkey_id' => $exkeyId]);
    }
}
