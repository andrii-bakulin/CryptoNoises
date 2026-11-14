<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssDbExp;
use CryptoCore\Vss\VssModel;

class Contracts extends VssModel
{
    const kMETADATA_STATE_Idle                  = 'idle';
    const kMETADATA_STATE_Syncing               = 'syncing';
    const kMETADATA_STATE_Success               = 'success';
    const kMETADATA_STATE_Error                 = 'error';

    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'contracts';
    }

    public function getItem(string $contractAddress): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['contract_address' => $contractAddress]);
    }

    public function open(int $bigmap, int $contractAdid, string $contractAddress, ?string $metadataUri): void
    {
        $item = [
            'contract_adid'     => $contractAdid,
            'contract_address'  => $contractAddress,
            'bigmap'            => $bigmap,
            'metadata_uri'      => $metadataUri,
        ];
        Vss::dbTezos()->insert($this->tableName(), $item, 'IGNORE');
    }

    public function update(int $contractAdid, array $data): void
    {
        Vss::dbTezos()->update($this->tableName(), $data, ['contract_adid'=>$contractAdid]);
    }

    public function incTokensCounter(int $contractAdid, string $contractAddress): void
    {
        $insert = [
            'contract_adid'     => $contractAdid,
            'contract_address'  => $contractAddress,
            'bigmap'            => null,
            'metadata_uri'      => null,
            'tokens_count'      => 1,
        ];

        $update = [
            'tokens_count' => VssDbExp::query('COALESCE(tokens_count,0) + 1')
        ];

        Vss::dbTezos()->insertOrUpdate($this->tableName(), $insert, $update);
    }
}
