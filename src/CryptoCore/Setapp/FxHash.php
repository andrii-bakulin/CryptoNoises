<?php

namespace CryptoCore\Setapp;

// v0 - this is fx(hash) project was in beta
// v1 - 1st release of fx(hash)               on ______________?
// v2 - 2nd release of fx(hash) as fx(params) on March 18, 2023?

class FxHash
{
    const COLLECTION_GROUP                      = 'fxhash';

    public static function GetTokensKTIDByVersion(?string $version): ?string
    {
        if ($version === null)
            return null;

        switch ($version)
        {
            case '0': return FxHash_v0::TokensKTID;
            case '1': return FxHash_v1::TokensKTID;
            case '2': return FxHash_v2::TokensKTID;
        }

        return null;
    }

    public static function GetTokensKTIDByIssuerTokenId(int $issuerTokenId): ?string
    {
        if ($issuerTokenId >= 26048)    return FxHash_v2::TokensKTID;
        if ($issuerTokenId >= 10965)    return FxHash_v1::TokensKTID;
        return FxHash_v0::TokensKTID;
    }

    public static function GetIssuersKTIDByIssuerTokenId(int $issuerTokenId): ?string
    {
        if ($issuerTokenId >= 26048)    return FxHash_v2::IssuerKTID;
        if ($issuerTokenId >= 10965)    return FxHash_v1::IssuerKTID;
        return FxHash_v0::IssuerKTID_v2;
    }
}
