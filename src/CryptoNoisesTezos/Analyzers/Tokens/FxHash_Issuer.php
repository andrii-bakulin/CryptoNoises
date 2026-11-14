<?php

namespace CryptoNoisesTezos\Analyzers\Tokens;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Jobs\AnalyzeNewlyDetectedToken;

class FxHash_Issuer
{
    public static function AnalyzeNewlyDetectedToken(AnalyzeNewlyDetectedToken $job, array $value): bool
    {
        if (!in_array($job->bigmap, [
            Setapp\FxHash_v0::Bigmap_Issuers,
            Setapp\FxHash_v1::Bigmap_Issuers,
            Setapp\FxHash_v2::Bigmap_Issuers,]))
            return false;

        $metadataUri = $job->token->unpackHex2Bin($value['metadata']);

        $job->token->open($metadataUri, $job->bigmap, $job->bigmapExkey, $job->level);
        $job->token->creatorAddress     = $value['author'];
        $job->token->editions           = $value['supply'];
        $job->token->royaltiesFixed_d4  = $value['royalties'] * 10;
        $job->token->saveTokenOnly();

        $job->lastActionNotice = "{$job->token->contractAdid}:{$job->token->tokenUnid} = $metadataUri";
        return true;
    }
}
