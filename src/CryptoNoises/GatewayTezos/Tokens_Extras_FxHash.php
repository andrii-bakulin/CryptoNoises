<?php

namespace CryptoNoises\GatewayTezos;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Core;
use CryptoNoisesTezos\Entities;

trait Tokens_Extras_FxHash
{
    private static function BuildTokenEntity_ExtraFix_FxHash(\CryptoNoises\Entities\Token $crynzToken,
                                                             \CryptoNoisesTezos\Entities\Token $tezosToken): void
    {
        if ($tezosToken->contractAddress == Setapp\FxHash_v1::TokensKTID)
            $issuerAddress = Setapp\FxHash_v1::IssuerKTID;
        else if ($tezosToken->contractAddress == Setapp\FxHash_v2::TokensKTID)
            $issuerAddress = Setapp\FxHash_v2::IssuerKTID;
        else
            return;

        if ($tezosToken->collectionId === null) // collectionId may be missing
            return;

        $issuerAdid = Core\TezosAddress::GetAdidByAddress($issuerAddress);
        $issuerUnid = Entities\Token::PackTokenIdToUnid($tezosToken->collectionId, false);

        if ($issuerAdid === null || $issuerUnid === null)
            return;

        $issuerToken = Entities\Token::GetByAdidAndTokenUnid($issuerAdid, $issuerUnid);

        if ($issuerToken === null)
            return;

        if ($tezosToken->name == '[WAITING TO BE SIGNED]')
        {
            $tezosToken->name           = $issuerToken->name;
            $tezosToken->description    = $issuerToken->description;
            $tezosToken->previewUri     = $issuerToken->previewUri;
        }

        $crynzToken->collectionName = $issuerToken->name;
    }
}
