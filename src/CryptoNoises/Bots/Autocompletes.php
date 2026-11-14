<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Entities\User;
use CryptoNoises\GatewayTezos;
use CryptoNoises\Localize\Phrase;
use CryptoNoises\Models;
use CryptoNoises\Networks\Tezos\Services\TezosDomains;
use CryptoNoises\Networks\Tezos\TezosNetwork;

class Autocompletes
{
    public static function FillAddress(User $user, string $searchQuery): array
    {
        $TOTAL_LIMIT = 10;

        $result = [];

        // #1
        if (TezosNetwork::IsWalletAddressPrefixValid($searchQuery))
        {
            $items = GatewayTezos\Models\Address::model()->search($searchQuery, $TOTAL_LIMIT);

            foreach ($items as $item)
            {
                $address = $item['address'];

                if (!TezosNetwork::IsWalletAddressValid($address))
                    continue;

                $result[$address] = Phrase::PackAutocompleteAddressValue($address, TezosDomains::GetDomainByAddress($address));
            }
        }

        // #2
        if (count($result) < $TOTAL_LIMIT)
        {
            $items = GatewayTezos\Models\DomainsRecords::model()->search($searchQuery, $TOTAL_LIMIT, true);

            foreach ($items as $item)
            {
                $address = $item['address'];

                if (!TezosNetwork::IsWalletAddressValid($address))
                    continue;

                if (!isset($result[$address]))
                    $result[$address] = Phrase::PackAutocompleteAddressValue($address, $item['domain']);
            }
        }

        // #3
        if (count($result) < $TOTAL_LIMIT)
        {
            $items = GatewayTezos\Models\DomainsRecords::model()->search($searchQuery, $TOTAL_LIMIT, false);

            foreach ($items as $item)
            {
                $address = $item['address'];

                if (!TezosNetwork::IsWalletAddressValid($address))
                    continue;

                if (!isset($result[$address]))
                    $result[$address] = Phrase::PackAutocompleteAddressValue($address, $item['domain']);
            }
        }

        ksort($result);
        return array_slice($result, 0, $TOTAL_LIMIT);
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function FillAddress_onDeleteUserWallet(User $user, string $searchQuery): array
    {
        $TOTAL_LIMIT = 25;

        $result = [];

        $items = Models\Wallets::model()->search($user->userId, $searchQuery, $TOTAL_LIMIT);

        foreach ($items as $item)
        {
            $address = $item['address'];
            $name    = $item['name'];

            if ($name == '')
                $name = TezosDomains::GetDomainByAddress($address);

            $result[$address] = Phrase::PackAutocompleteAddressValue($address, $name);
        }

        ksort($result);
        return array_slice($result, 0, $TOTAL_LIMIT);
    }

    public static function FillAddress_onUnfollow(User $user, string $searchQuery): array
    {
        $TOTAL_LIMIT = 25;

        $result = [];

        $items = Models\UsersFollow::model()->search($user->userId, $searchQuery, $TOTAL_LIMIT);

        foreach ($items as $item)
        {
            $address = $item['address'];
            $name    = TezosDomains::GetDomainByAddress($address);

            $result[$address] = Phrase::PackAutocompleteAddressValue($address, $name);
        }

        ksort($result);
        return array_slice($result, 0, $TOTAL_LIMIT);
    }
}
