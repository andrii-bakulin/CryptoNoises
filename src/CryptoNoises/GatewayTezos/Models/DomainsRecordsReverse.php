<?php

namespace CryptoNoises\GatewayTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class DomainsRecordsReverse extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return \CryptoNoisesTezos\Models\DomainsRecordsReverse::model()->tableName();
    }

    public function getItemByAddress(string $address): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['address' => $address]);
    }
}
