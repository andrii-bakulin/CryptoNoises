<?php

namespace CryptoNoisesTezos\Models\Log;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TzprofilesCache extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return "tzprofiles_cache";
    }

    public function save(string $profileAddress, string $content): void
    {
        $data = [
            'profile_address' => $profileAddress,
            'content'         => $content,
        ];
        Vss::dbTezosLog()->insertOrUpdate($this->tableName(), $data, $data);
    }
}
