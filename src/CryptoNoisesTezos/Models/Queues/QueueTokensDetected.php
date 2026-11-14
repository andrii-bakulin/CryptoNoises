<?php

namespace CryptoNoisesTezos\Models\Queues;

class QueueTokensDetected extends QueueAbstract
{
    public string $selectMode                   = self::kSELECT_MODE_FirstInFirstOut;
    public int $maxTryCount                     = 3;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'queue_tokens_detected';
    }
}
