<?php

namespace CryptoNoisesTezos\Analyzers\TransactionHunters;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Core\Log;
use CryptoNoisesTezos\Entities;

class Tokens_FxHash_TokenInstance_v2 extends AbstractHunter
{
    /**
     * Handles minting a token instance from an issuer
     * (not the issuer mint itself, see CryptoNoisesTezos\Analyzers\Tokens\FxHash_Issuer)
     */
    public function analyze(array $recInit, array $records): bool
    {
        if ($recInit['target_address'] != Setapp\FxHash_v2::IssuerKTID)
        {
            // Something was minted via the 'make_claim' entrypoint on another contract

            if ($recInit['entrypoint'] != 'make_claim')
                return false;

            $recInit = self::ExtractRecordByTargetAddress($records, Setapp\FxHash_v2::IssuerKTID);

            if ($recInit === null)
                return false;
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($recInit['entrypoint'] != 'mint' && $recInit['entrypoint'] != 'mint_with_ticket')
            return false;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $recMint = self::ExtractRecordByEntrypoint($records, 'mint');

        if ($recMint == null)
        {
            Log::Warning('Tokens_FxHash_TokenInstance_v2', 'Mint', 'Cannot find mint sub-record', $recInit);
            return false;
        }

        if ($recMint['target_address'] == Setapp\FxHash_v2::TicketsKTID)
        {
            // This is a ticket mint event: no need to parse it
            return false;
        }

        $value = $recMint['parameter']['value'];

        $tokenIssuer = Entities\Token::GetByAddressAndTokenId(Setapp\FxHash_v2::IssuerKTID, $value['issuer_id']);

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
