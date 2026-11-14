<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokenUnids extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'token_unids';
    }

    public function getTokenUnidByTokenValue(string $tokenValue, bool $autoCreate): ?int
    {
        $item = $this->getTokenItemByValue($tokenValue);

        if (empty($item))
        {
            if ($autoCreate == false)
                return null;

            Vss::dbTezos()->insert($this->tableName(), ['token_value' => $tokenValue], 'IGNORE');

            $item = $this->getTokenItemByValue($tokenValue);
        }

        return $item['token_unid'];
    }

    private function getTokenItemByValue(string $tokenValue): ?array
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE token_value = :token_value";

        return Vss::dbTezos()->queryRow($sql, ['token_value'=>$tokenValue]);
    }

    public function getTokenValueByTokenUnid(int $tokenUnid): ?string
    {
        // Unids are stored negated in bigmap exkeys (exkey_token_id = -token_unid): accept both signs
        $tokenUnid = abs($tokenUnid);

        $sql = "SELECT token_value FROM {$this->tableName()} WHERE token_unid = :token_unid";

        return Vss::dbTezos()->queryScalar($sql, ['token_unid'=>$tokenUnid]);
    }
}
