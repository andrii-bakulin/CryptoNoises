<?php

namespace CryptoNoises\GatewayTezos;

use CryptoNoises\Networks\Tezos\Services\TezosDomains;
use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;

class Wallets
{
    public static function GetWalletPublicName(string $profileAddress): ?string
    {
        $profileAdid = TezosAddress::GetAdidByAddress($profileAddress);

        if ($profileAdid === null)
            return null;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $itemTzprofiles = Models\ProfilesTzprofiles::model()->getItem($profileAdid);

        if ($itemTzprofiles && $itemTzprofiles['alias'])
            return $itemTzprofiles['alias'];

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $itemTzkt = Models\ProfilesTzkt::model()->getItem($profileAdid);

        if ($itemTzkt && $itemTzkt['alias'])
            return $itemTzkt['alias'];

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $domain = TezosDomains::GetDomainByAddress($profileAddress);

        if ($domain)
            return $domain;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        return null;
    }

    public static function GetWalletBalance(string $address): ?int
    {
        $data = \CryptoNoisesTezos\Vendors\Tzkt::GetAccountDetails($address);
        return $data ? $data['balance'] : null;
    }
}
