<?php

namespace CryptoNoisesTezos\Models\Queues;

class QueueTokensMetadataMerge extends QueueAbstract
{
    public string $selectMode                   = self::kSELECT_MODE_Anyone;
    public int $maxTryCount                     = 32;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'queue_tokens_metadata_merge';
    }
}
