<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\PostActions;

use CryptoNoisesTezos\Core\Bigmap;
use CryptoNoisesTezos\Models;

class TokenDetector_TokenMetadata implements PostActionInterface
{
    public function save(int $bigmap, int $level, $key, string $cValString): void
    {
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
