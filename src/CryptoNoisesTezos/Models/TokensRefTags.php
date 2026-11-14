<?php

namespace CryptoNoisesTezos\Models;

use CryptoNoisesTezos\Entities\TokenTag;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensRefTags extends VssModel
{
    use Tokens_Cleanup;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_ref_tags';
    }

    /**
     * @param TokenTag[] $records
     */
    public function saveRecords(int $contractAdid, int $tokenUnid, array $records): void
    {
        $this->cleanUp($contractAdid, $tokenUnid);

        foreach ($records as $record)
        {
            Vss::dbTezos()->insert($this->tableName(), [
                'contract_adid' => $contractAdid,
                'token_unid'    => $tokenUnid,
                'token_tag_id'  => $record->tagId,
            ], 'IGNORE');
        }
    }
}
