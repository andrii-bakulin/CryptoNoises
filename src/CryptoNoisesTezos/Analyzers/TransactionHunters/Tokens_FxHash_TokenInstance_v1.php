<?php

namespace CryptoNoisesTezos\Analyzers\TransactionHunters;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Core\Log;
use CryptoNoisesTezos\Entities;

class Tokens_FxHash_TokenInstance_v1 extends AbstractHunter
{
    /**
     * Handles minting a token instance from an issuer
     * (not the issuer mint itself, see CryptoNoisesTezos\Analyzers\Tokens\FxHash_Issuer)
     */
    public function analyze(array $recInit, array $records): bool
    {
        if ($recInit['target_address'] != Setapp\FxHash_v1::IssuerKTID)
            return false;

        if ($recInit['entrypoint'] != 'mint')
            return false;

        $recMint = self::ExtractRecordByEntrypoint($records, 'mint');

        if ($recMint == null)
        {
            Log::Warning('Tokens_FxHash_TokenInstance_v1', 'Mint', 'Cannot find mint sub-record', $recInit);
            return false;
        }

        $value = $recMint['parameter']['value'];

        $tokenIssuer = Entities\Token::GetByAddressAndTokenId(Setapp\FxHash_v1::IssuerKTID, $value['issuer_id']);

        $tokenAppend = Entities\TokenAppend::Dummy($recMint['target_address'], $value['token_id']);
        $tokenAppend->minterAddress     = $value['address'];
        $tokenAppend->creatorAddress    = $tokenIssuer ? $tokenIssuer->creatorAddress : null;
        $tokenAppend->editions          = 1;
        $tokenAppend->royaltiesFixed_d4 = $value['royalties'] * 10;
        $tokenAppend->collectionGroup   = Setapp\FxHash::COLLECTION_GROUP;
        $tokenAppend->collectionId      = $value['issuer_id'];
        $tokenAppend->save();

        return true;
    }
}
