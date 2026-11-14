<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;

trait Tokens_Cleanup
{
    public function cleanUp(int $contractAdid, int $tokenUnid): void
    {
        Vss::dbTezos()->delete($this->tableName(), [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
        ]);
    }
}
