<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\PostActions;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Core\Bigmap;
use CryptoNoisesTezos\Models;

class TokenDetector_Basic implements PostActionInterface
{
    public function save(int $bigmap, int $level, $key, string $cValString): void
    {
        if (!in_array($bigmap, [
            Setapp\FxHash_v0::Bigmap_Issuers,
            Setapp\FxHash_v1::Bigmap_Issuers,
            Setapp\FxHash_v2::Bigmap_Issuers,]))
            return;

        Models\Queues\QueueTokensDetected::model()->push([
            'bigmap'       => $bigmap,
            'bigmap_exkey' => Bigmap::KeyToExkey($key),
            'level'        => $level,
        ]);
    }

    public function remove(int $bigmap, int $level, $key): void
    {
        // nothing to do
    }

    public function removeAllInBigmap(int $bigmap, int $level): void
    {
        // nothing to do
    }
}
