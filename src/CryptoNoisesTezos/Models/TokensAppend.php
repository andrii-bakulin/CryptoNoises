<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensAppend extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_append';
    }

    public function getItem(int $contractAdid, int $tokenUnid): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
        ]);
    }

    public function save(int $contractAdid, int $tokenUnid, array $data): bool
    {
        $data['contract_adid'] = $contractAdid;
        $data['token_unid']    = $tokenUnid;

        return (bool) Vss::dbTezos()->insertOrUpdate($this->tableName(), $data, $data);
    }

    public function delete(int $contractAdid, int $tokenUnid): void
    {
        Vss::dbTezos()->delete($this->tableName(), [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
        ]);
    }
}
