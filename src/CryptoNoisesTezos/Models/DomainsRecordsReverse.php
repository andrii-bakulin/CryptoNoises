<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;
use CryptoCore\Vss\VssDbExp;

class DomainsRecordsReverse extends VssModel
{
    const kSTATE_Active     = 'active';
    const kSTATE_Removed    = 'removed';

    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'domains_records_reverse';
    }

    public function save(string $address, array $item): void
    {
        $item['address']        = $address ?: null;
        $item['state']          = self::kSTATE_Active;
        $item['internal_data']  = $item['internal_data'] ? json_encode($item['internal_data']) : null;

        $insertData = $item;

        $updateData = $item;
        $updateData['ts_updated']   = VssDbExp::now();

        Vss::dbTezos()->insertOrUpdate($this->tableName(), $insertData, $updateData);
    }

    public function remove(string $address): void
    {
        Vss::dbTezos()->update($this->tableName(),
            ['state' => self::kSTATE_Removed],
            ['address' => $address]);
    }
}
