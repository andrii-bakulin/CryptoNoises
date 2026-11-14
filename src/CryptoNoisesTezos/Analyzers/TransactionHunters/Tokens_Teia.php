<?php

namespace CryptoNoisesTezos\Analyzers\TransactionHunters;

use CryptoNoisesTezos\Entities;

class Tokens_Teia extends AbstractHunter
{
    const TokensKTID                    = 'KT1RJ6PbjHpwc3M5rw5s2Nbmefwbuwbdxton';   # hic et nunc NFTs
    const MinterAddress                 = 'KT1Hkg5qeNhfwpKW4fXvq7HGZB9z2EnmCCA9';   # hic et nunc Minter

    public function analyze(array $recInit, array $records): bool
    {
        if ($recInit['target_address'] != self::MinterAddress)
            return false;

        if ($recInit['entrypoint'] != 'mint_OBJKT')
            return false;

        $recMint = self::ExtractRecordByEntrypoint($records, 'mint');

        if ($recMint == null || $recMint['target_address'] != self::TokensKTID)
            return false;

        $tokenAppend = Entities\TokenAppend::Dummy(self::TokensKTID, $recMint['parameter']['value']['token_id']);
        $tokenAppend->creatorAddress    = $recInit['sender_address'];
        $tokenAppend->editions          = $recInit['parameter']['value']['amount'];
        $tokenAppend->royaltiesFixed_d4 = $recInit['parameter']['value']['royalties'] * 10;
        $tokenAppend->save();

        return true;
    }
}
