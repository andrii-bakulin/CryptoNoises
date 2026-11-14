<?php

namespace CryptoNoisesTezos\Models\Log;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class IpfsCache extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName($ipfsId): string
    {
        $groupId = substr(sha1($ipfsId), 0, 1);
        return "ipfs_cache_{$groupId}";
    }

    private function normalizeIpfsId(string $ipfsId): ?string
    {
        if (mb_strlen($ipfsId) > 255)
            return null;

        // ipfs://QmbY5g2LcPNTFjiSjAvEEhQN3oSwggKm1TCapUNP9iVCR8
        if (preg_match('#^(ipfs|https|http)://#iu', $ipfsId) > 0)
            return $ipfsId;

        // QmPnKG1u8iU3owL86wbtUCcZak9Q9ZaeHv19FvYttLago3
        if (preg_match('#^Qm([A-Za-z0-9]{44})$#', $ipfsId) > 0)
            return 'ipfs://'.$ipfsId;

        return null;
    }

    public function getJson(string $ipfsId) // : mixed
    {
        if (($ipfsId = $this->normalizeIpfsId($ipfsId)) === null)
            return null;

        $jsonString = Vss::dbTezosLog()->selectScalar('ipfs_data',
            $this->tableName($ipfsId),
            ['ipfs_id' => $ipfsId]);

        return $jsonString ? json_decode($jsonString, true) : null;
    }

    public function save(string $ipfsId, string $ipfsData): void
    {
        if (($ipfsId = $this->normalizeIpfsId($ipfsId)) === null)
            return;

        if (mb_strlen($ipfsData) > 16384)
            return;

        Vss::dbTezosLog()->insert($this->tableName($ipfsId), [
            'ipfs_id'   => $ipfsId,
            'ipfs_data' => $ipfsData,
        ], 'IGNORE');
    }
}
