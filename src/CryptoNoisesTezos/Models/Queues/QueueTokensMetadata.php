<?php

namespace CryptoNoisesTezos\Models\Queues;

class QueueTokensMetadata extends QueueAbstract
{
    public string $selectMode                   = self::kSELECT_MODE_Normal;
    public int $maxTryCount                     = 16;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'queue_tokens_metadata';
    }
}
