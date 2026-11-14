<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensRefCollection extends VssModel
{
    use Tokens_Cleanup;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_ref_collection';
    }

    public function getItem(int $contractAdid, int $tokenUnid): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid
        ]);
    }

    public function save(int $contractAdid, int $tokenUnid, ?array $data): void
    {
        $this->cleanUp($contractAdid, $tokenUnid);

        $data['contract_adid'] = $contractAdid;
        $data['token_unid']    = $tokenUnid;

        Vss::dbTezos()->insert($this->tableName(), $data, 'IGNORE');
    }
}
