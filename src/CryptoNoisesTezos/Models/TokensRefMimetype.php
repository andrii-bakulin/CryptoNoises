<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensRefMimetype extends VssModel
{
    use Tokens_Cleanup;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_ref_mimetypes';
    }

    public function save(int $contractAdid, int $tokenUnid, int $mimetypeId): void
    {
        $this->cleanUp($contractAdid, $tokenUnid);

        Vss::dbTezos()->insert($this->tableName(), [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
            'mimetype_id'   => $mimetypeId,
        ], 'IGNORE');
    }
}
