<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;
use CryptoCore\Vss\VssDbExp;

class DomainsRecords extends VssModel
{
    const kSTATE_Active     = 'active';
    const kSTATE_Removed    = 'removed';

    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'domains_records';
    }

    public function save(string $domain, array $item): void
    {
        $item['domain']         = $domain;
        $item['state']          = self::kSTATE_Active;
        $item['data']           = $item['data'] ? json_encode($item['data']) : null;
        $item['internal_data']  = $item['internal_data'] ? json_encode($item['internal_data']) : null;

        $insertData = $item;

        $updateData = $item;
        $updateData['ts_updated']   = VssDbExp::now();

        Vss::dbTezos()->insertOrUpdate($this->tableName(), $insertData, $updateData);
    }

    public function remove(string $domain): void
    {
        Vss::dbTezos()->update($this->tableName(),
            ['state' => self::kSTATE_Removed],
            ['domain' => $domain]);
    }
}
