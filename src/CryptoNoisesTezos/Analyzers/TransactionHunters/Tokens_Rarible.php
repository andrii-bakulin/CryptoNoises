<?php

namespace CryptoNoisesTezos\Analyzers\TransactionHunters;

use CryptoNoisesTezos\Entities;

class Tokens_Rarible extends AbstractHunter
{
    const MinterAddress                 = 'KT18pVpRXKPY2c4U2yFEGSH3ZnhB2kL8kwXS';   # Rarible

    public function analyze(array $recInit, array $records): bool
    {
        if ($recInit['target_address'] != self::MinterAddress)
            return false;

        if ($recInit['entrypoint'] != 'mint')
            return false;

        // Notice: Rarible has mint-data in init-record

        $tokenAppend = Entities\TokenAppend::Dummy($recInit['target_address'], $recInit['parameter']['value']['itokenid']);
        $tokenAppend->creatorAddress = $recInit['sender_address'];

        $tokenAppend->royaltiesFixed_d4 = 0;

        foreach ($recInit['parameter']['value']['iroyalties'] as $royItem)
            $tokenAppend->royaltiesFixed_d4 += $royItem['partValue'];

        if ($tokenAppend->royaltiesFixed_d4 === 0)
            $tokenAppend->royaltiesFixed_d4 = null;

        $tokenAppend->save();

        return true;
    }
}
