<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class IpfsMedia extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'ipfs_media';
    }

    public function getItem(string $uri): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['ipfs_uri' => $uri]);
    }

    public function save(string $uri, array $data): bool
    {
        $data['ipfs_uri'] = $uri;

        return (bool) Vss::dbTezos()->insertOrUpdate($this->tableName(), $data, $data);
    }
}
