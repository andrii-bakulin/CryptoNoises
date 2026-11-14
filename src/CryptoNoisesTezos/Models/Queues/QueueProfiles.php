<?php

namespace CryptoNoisesTezos\Models\Queues;

class QueueProfiles extends QueueAbstract
{
    const kPROFILE_TYPE_tzkt                    = 'tzkt';
    const kPROFILE_TYPE_tzprofiles              = 'tzprofiles';

    public string $selectMode                   = self::kSELECT_MODE_Anyone;
    public int $maxTryCount                     = 8;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'queue_profiles';
    }
}
