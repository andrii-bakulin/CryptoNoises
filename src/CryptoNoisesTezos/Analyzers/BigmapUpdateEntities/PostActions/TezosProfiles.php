<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\PostActions;

use CryptoNoisesTezos\Core\Helper;
use CryptoNoisesTezos\Core\Log;
use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;
use Exception;

class TezosProfiles implements PostActionInterface
{
    public function save(int $bigmap, int $level, $key, string $cValString): void
    {
        if ($key !== '')
            return;

        try
        {
            $cValue = json_decode($cValString, true);

            if (is_array($cValue) || is_object($cValue) ||
                strlen($cValue)%2 != 0 ||
                strpos($cValue, '68747470733a2f2f747a70726f66696c65732e636f6d2f') === false)
                return;

            //  68747470733a2f2f747a70726f66696c65732e636f6d2f747a69703031365f6d657461646174612e6a736f6e
            $cValue = Helper::UnpackHex2Bin($cValue);
            //  https://tzprofiles.com/tzip016_metadata.json

            if ($cValue == null)
                return;

            if ($cValue == 'https://tzprofiles.com/tzip016_metadata.json')
            {
                $contractAdid = TezosAddress::GetOrCreateAdid(Models\Bigmaps::model()->getContractByBigmap($bigmap));

                if ($contractAdid == null)
                {
                    Log::Warning('TezosProfiles', 'save', 'Cannot detect $contractAdid value', [
                        'bigmap' => $bigmap,
                        'level'  => $level,
                        'value'  => $cValString,
                    ]);
                    return;
                }

                Models\Queues\QueueProfiles::model()->push([
                    'profile_type'  => Models\Queues\QueueProfiles::kPROFILE_TYPE_tzprofiles,
                    'source_adid'   => $contractAdid,
                ], Models\Queues\QueueAbstract::kSyncMode_New);
            }
            else if (strpos($cValue, 'https://tzprofiles.com/') === 0)
            {
                Log::Warning('TezosProfiles', 'save', 'Found undefined metadata-value for tzprofiles.com', [
                    'bigmap' => $bigmap,
                    'level'  => $level,
                    'value'  => $cValString,
                ]);
            }
        }
        catch (Exception $e)
        {
            Log::Warning('TezosProfiles', 'save', $e->getMessage(), [
                'bigmap' => $bigmap,
                'level'  => $level,
            ]);
        }
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
