<?php

namespace CryptoNoises\GatewayTezos\Models;

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
        return \CryptoNoisesTezos\Models\Address::model()->tableName();
    }

    public function search(string $query, int $limit): ?array
    {
        $query = preg_replace('/[%_]/', '', $query); // to prevent MySQL long query in LIKE rule

        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE address_type = :type_wallet
                AND address_ci LIKE :address
            LIMIT :limit
        ";

        return Vss::dbTezos()->query($sql, [
            'type_wallet'   => \CryptoNoisesTezos\Models\Address::TYPE_Wallet,
            'address'       => $query.'%',
            'limit'         => $limit,
        ]);
    }
}
