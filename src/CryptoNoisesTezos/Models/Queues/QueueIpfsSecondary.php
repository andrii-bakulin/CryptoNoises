<?php

namespace CryptoNoisesTezos\Models\Queues;

class QueueIpfsSecondary extends QueueAbstract
{
    public string $selectMode                   = self::kSELECT_MODE_Anyone;
    public int $maxTryCount                     = 3;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'queue_ipfs_secondary';
    }
}
