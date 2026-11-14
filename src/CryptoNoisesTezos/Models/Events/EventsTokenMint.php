<?php

namespace CryptoNoisesTezos\Models\Events;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class EventsTokenMint extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'events_token_mint';
    }

    public function fix(int $contractAdid, int $tokenUnid): int
    {
        $item = [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
        ];
        return (int) Vss::dbTezos()->insert($this->tableName(), $item, 'IGNORE');
    }
}
