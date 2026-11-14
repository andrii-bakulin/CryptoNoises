<?php

namespace CryptoNoisesTezos\Models;

use CryptoNoisesTezos\Entities\TokenAttribute;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensAttributes extends VssModel
{
    use Tokens_Cleanup;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_attributes';
    }

    /**
     * @param int $contractAdid
     * @param int $tokenUnid
     * @param TokenAttribute[] $records
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
                'name'          => $record->name,
                'value'         => $record->value,
                'type'          => $record->type,
            ], 'IGNORE');
        }
    }

    public function selectAttributesForToken(int $contractAdid, int $tokenUnid): array
    {
        $sql = "
            SELECT `name`, `value`
              FROM {$this->tableName()} 
             WHERE contract_adid = :contract_adid
               AND token_unid = :token_unid
            ORDER BY 1
        ";

        return Vss::dbTezos()->query($sql, [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid
        ]);
    }
}
