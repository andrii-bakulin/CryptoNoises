<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensUndefinedKeys extends VssModel
{
    use Tokens_Cleanup;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_undefined_keys';
    }

    /**
     * @param mixed $value
     */
    public function saveRecord(int $contractAdid, int $tokenUnid, string $key, $value): void
    {
        Vss::dbTezos()->insert($this->tableName(), [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
            'key'           => $key,
            'value_json'    => json_encode($value),
        ], 'IGNORE');
    }
}
