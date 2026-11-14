<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Tokens extends VssModel
{
    const kMETADATA_STATE_Idle      = 'idle';
    const kMETADATA_STATE_Syncing   = 'syncing';
    const kMETADATA_STATE_Success   = 'success';
    const kMETADATA_STATE_Error     = 'error';
    const kMETADATA_STATE_Empty     = 'empty';

    const kPREVIEW_STATE_Idle       = 'idle';
    const kPREVIEW_STATE_Syncing    = 'syncing';
    const kPREVIEW_STATE_Success    = 'success';
    const kPREVIEW_STATE_Error      = 'error';
    const kPREVIEW_STATE_Empty      = 'empty';
    const kPREVIEW_STATE_Teaser     = 'teaser';

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens';
    }

    public function getItem(int $contractAdid, int $tokenUnid): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
        ]);
    }

    public function open(int $contractAdid, string $contractAddress, int $tokenUnid, array $data): bool
    {
        $data['contract_adid']      = $contractAdid;
        $data['contract_address']   = $contractAddress;
        $data['token_unid']         = $tokenUnid;

        return (bool) Vss::dbTezos()->insert($this->tableName(), $data, 'IGNORE');
    }

    public function update(int $contractAdid, int $tokenUnid, array $data): bool
    {
        return (bool) Vss::dbTezos()->update($this->tableName(), $data, [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
        ]);
    }
}
