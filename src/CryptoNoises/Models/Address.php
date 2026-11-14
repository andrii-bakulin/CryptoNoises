<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Address extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'address';
    }

    public function getItemByAdid(int $adid): ?array
    {
        return Vss::db()->selectRow('*', $this->tableName(), ['adid' => $adid]);
    }

    public function getItemByAddress(?string $address): ?array
    {
        return $address
            ? Vss::db()->selectRow('*', $this->tableName(), ['address' => $address])
            : null;
    }

    public function create(string $address): void
    {
        Vss::db()->insert($this->tableName(), [
            'address'    => $address,
            'address_ci' => $address,
        ], 'IGNORE');
    }
}
