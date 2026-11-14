<?php

namespace CryptoNoisesTezos\Analyzers\TransactionHunters;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Entities;

class Tokens_Objkt extends AbstractHunter
{
    const MinterAddress                             = Setapp\ObjktCom_v1::MinterKTID;

    public function analyze(array $recInit, array $records): bool
    {
        if ($recInit['target_address'] != self::MinterAddress)
            return false;

        if ($recInit['entrypoint'] != 'mint_artist')
            return false;

        while ($recMint = self::ExtractRecordByEntrypoint($records, 'mint'))
        {
            $tokenAppend = Entities\TokenAppend::Dummy($recMint['target_address'], $recMint['parameter']['value']['token_id']);
            $tokenAppend->editions        = $recMint['parameter']['value']['amount'];
            $tokenAppend->collectionGroup = \CryptoCore\Setapp\ObjktCom::COLLECTION_GROUP;
            $tokenAppend->collectionId    = $recInit['parameter']['value']['collection_id'];
            $tokenAppend->save();
        }

        return true;
    }
}
