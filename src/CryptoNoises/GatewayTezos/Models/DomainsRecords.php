<?php

namespace CryptoNoises\GatewayTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class DomainsRecords extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return \CryptoNoisesTezos\Models\DomainsRecords::model()->tableName();
    }

    public function getItemByDomain(string $domain): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['domain' => $domain]);
    }

    public function selectDomainsByOwner(string $address): array
    {
        return Vss::dbTezos()->selectColumn('domain', $this->tableName(), [
            'owner' => $address,
            'level' => 2,
        ]);
    }

    public function search(string $query, int $limit, bool $isPrefix): ?array
    {
        $query = preg_replace('/[%_]/', '', $query); // to prevent MySQL long query in LIKE rule

        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE state = :state_active
                AND domain LIKE :domain
                AND address IS NOT NULL
            LIMIT :limit
        ";

        return Vss::dbTezos()->query($sql, [
            'state_active'  => \CryptoNoisesTezos\Models\DomainsRecords::kSTATE_Active,
            'domain'        => $isPrefix ? $query.'%' : '%'.$query.'%',
            'limit'         => $limit,
        ]);
    }
}
