<?php

namespace CryptoNoisesTezos\Analyzers\Tokens;

use CryptoNoisesTezos\Core\Bigmap;
use CryptoNoisesTezos\Core\Helper;
use CryptoNoisesTezos\Entities;
use CryptoNoisesTezos\Jobs\AnalyzeNewlyDetectedToken;
use Exception;

class TezosDomains
{
    const kBIGMAP_DOMAIN_DATA                   = 1264;

    const kCRYPTONOISES_MARKER_METADATA         = 'cryptonoises://token-metadata/tezos-domain';

    //------------------------------------------------------------------------------------------------------------------

    public static function AnalyzeNewlyDetectedToken(AnalyzeNewlyDetectedToken $job, string $contractAddress, array $value): bool
    {
        if ($job->bigmap != self::kBIGMAP_DOMAIN_DATA)
            return false;

        $topDomainTokenId = $value['tzip12_token_id'];

        if ($topDomainTokenId === null)
        {
            $job->lastActionNotice = "Skip: no token for this record (subdomain)";
            return true; // true: handled, stop further processing
        }

        $metadataUri = self::kCRYPTONOISES_MARKER_METADATA;

        $token = Entities\Token::DummyByAddressAndTokenId($contractAddress, $topDomainTokenId);

        $token->open($metadataUri, $job->bigmap, $job->bigmapExkey, $job->level);
        $token->tokenType = Entities\Token::kTOKEN_TYPE_Domain;
        // the rest of the metadata is built in GetJsonMetadata()
        $token->saveTokenOnly();

        $job->lastActionNotice = "{$token->contractAdid}:{$token->tokenUnid} = $metadataUri";
        return true;
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * Need convert IN-structure to the structure I normally analyze by core logic
     *
     * @param Entities\Token $token
     * @return array|null
     * @throws Exception
     */
    public static function GetJsonMetadata(Entities\Token $token): ?array
    {
        $key = Bigmap::ExkeyToKey($token->bigmapExkey);

        $domain = Helper::UnpackHex2Bin($key);

        $jsonOut = [];
        $jsonOut['name']        = $domain;
        $jsonOut['editions']    = 1;
        return $jsonOut;
    }
}
