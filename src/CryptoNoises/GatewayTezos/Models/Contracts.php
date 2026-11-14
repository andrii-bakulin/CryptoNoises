<?php

namespace CryptoNoises\GatewayTezos\Models;

use CryptoCore\Vss\VssModel;

class Contracts extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return \CryptoNoisesTezos\Models\Contracts::model()->tableName();
    }
}
