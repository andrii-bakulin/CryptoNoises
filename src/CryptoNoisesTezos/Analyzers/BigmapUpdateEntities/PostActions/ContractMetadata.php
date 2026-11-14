<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\PostActions;

use CryptoNoisesTezos\Core\Helper;
use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;
use Exception;

class ContractMetadata implements PostActionInterface
{
    public function save(int $bigmap, int $level, $key, string $cValString): void
    {
        if ($key !== '')
            return;

        $queueItem = [];

        $contractAddress = null;
        $contractAdid    = null;
        $metadataUri     = null;

        try
        {
            $bigmapItem = Models\Bigmaps::model()->getItem($bigmap);

            if ($bigmapItem == null)
                throw new Exception("Cannot find bigmap-item #$bigmap");

            $contractAddress = $bigmapItem['contract'];
            $contractAdid    = TezosAddress::GetOrCreateAdid($contractAddress);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $valDecoded = json_decode($cValString, true);

            if (is_array($valDecoded) || is_object($valDecoded))
                throw new Exception('$decodedValue is object or array');

            if (strlen($valDecoded)%2 != 0)
                throw new Exception('$decodedValue str-length is not even');

            $metadataUri = Helper::UnpackHex2Bin($valDecoded);

            if ($metadataUri === null)
                throw new Exception('Cannot unpack $valDecoded ['.$valDecoded.']');
        }
        catch (Exception $e)
        {
            $queueItem['state']         = Models\Queues\QueueAbstract::kState_Error;
            $queueItem['last_notice']   = $e->getMessage();
        }

        if ($contractAdid !== null)
            Models\Contracts::model()->open($bigmap, $contractAdid, $contractAddress, $metadataUri);

        $queueItem['bigmap']        = $bigmap;
        $queueItem['contract_adid'] = $contractAdid;
        $queueItem['metadata_uri']  = $metadataUri;
        Models\Queues\QueueContracts::model()->push($queueItem);
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
