<?php

namespace CryptoNoisesTezos\Analyzers\TransactionHunters;

use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;

class Profiles_Tzprofiles extends AbstractHunter
{
    public function analyze(array $recInit, array $records): bool
    {
        if ($recInit['entrypoint'] != 'default')
            return false;

        if (!TezosAddress::IsWalletAddressValid($recInit['sender_address']) ||
            !TezosAddress::IsContractAddressValid($recInit['target_address']))
            return false;

        $contractAdid = TezosAddress::GetAdidByAddress($recInit['target_address']);

        if (Models\ProfilesTzprofilesContracts::model()->isContractExists($contractAdid) == false)
            return false;

        Models\Queues\QueueProfiles::model()->push([
            'profile_type'  => Models\Queues\QueueProfiles::kPROFILE_TYPE_tzprofiles,
            'source_adid'   => $contractAdid,
        ], Models\Queues\QueueAbstract::kSyncMode_Update);

        return true;
    }
}
