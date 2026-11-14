<?php

namespace CryptoNoisesTezos\Models;

use CryptoNoisesTezos\Entities\TokenCreator;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensCreators extends VssModel
{
    use Tokens_Cleanup;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_creators';
    }

    /**
     * @param int $contractAdid
     * @param int $tokenUnid
     * @param TokenCreator[] $records
     * @return void
     */
    public function saveRecords(int $contractAdid, int $tokenUnid, array $records): void
    {
        $this->cleanUp($contractAdid, $tokenUnid);

        foreach ($records as $record)
        {
            Vss::dbTezos()->insert($this->tableName(), [
                'contract_adid' => $contractAdid,
                'token_unid'    => $tokenUnid,
                'creator_adid'  => $record->creatorAdid,
            ], 'IGNORE');
        }
    }
}
