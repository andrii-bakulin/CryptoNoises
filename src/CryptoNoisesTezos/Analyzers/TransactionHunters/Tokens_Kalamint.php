<?php

namespace CryptoNoisesTezos\Analyzers\TransactionHunters;

use CryptoNoisesTezos\Entities;

class Tokens_Kalamint extends AbstractHunter
{
    const ContractAddress               = 'KT1EpGgjQs73QfFJs9z7m1Mxm5MTnpC2tqse';   # Kalamint Art House

    public function analyze(array $recInit, array $records): bool
    {
        if ($recInit['target_address'] != self::ContractAddress)
            return false;

        if ($recInit['entrypoint'] != 'mint')
            return false;

        // Notice: Kalamint has mint-data in init-record

        $tokenId0 = $recInit['parameter']['value']['token_id'];
        $editions = (int) $recInit['parameter']['value']['editions'];

        for ($i=0; $i<$editions; $i++)
        {
            $tokenAppend = Entities\TokenAppend::Dummy($recInit['target_address'], $tokenId0 + $i);
            $tokenAppend->editionIndex      = 1 + $i;
            $tokenAppend->royaltiesFixed_d4 = $recInit['parameter']['value']['creator_royalty'] * 100;
            $tokenAppend->save();
        }

        return true;
    }
}
