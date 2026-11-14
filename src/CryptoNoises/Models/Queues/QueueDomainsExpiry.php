<?php

namespace CryptoNoises\Models\Queues;

class QueueDomainsExpiry extends QueueAbstract
{
    public string $selectMode                   = self::kSELECT_MODE_Anyone;
    public int $maxTryCount                     = 1;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'queue_domains_expiry';
    }
}
