<?php

namespace CryptoNoises\Models\Shared;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class MarketUrls extends VssModel
{
    const kDefaultTezos_Collection  = 'collection/tezos';
    const kDefaultTezos_Contract    = 'contract/tezos';
    const kDefaultTezos_Token       = 'token/tezos';
    const kDefaultTezos_User        = 'user/tezos';

    const kTokenFxhash              = 'token/fxhash';

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'market_urls';
    }

    public function getItemById(?string $marketUrlId): ?array
    {
        if ($marketUrlId == null)
            return null;

        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE market_url_id = :market_url_id
        ";

        return Vss::dbShared()->queryRow($sql, [
            'market_url_id' => $marketUrlId,
        ]);
    }
}
