<?php

namespace CryptoNoises\Networks\Tezos\Services;

class TezosDomains
{
    public static function GetAddressByDomain(?string $domain): ?string
    {
        if ($domain == '')
            return null;

        $domain = strtolower(trim($domain));

        $item = \CryptoNoises\GatewayTezos\Models\DomainsRecords::model()->getItemByDomain($domain);

        return $item ? $item['address'] : null;
    }

    public static function GetDomainByAddress(?string $address): ?string
    {
        if ($address == null)
            return null;

        $item = \CryptoNoises\GatewayTezos\Models\DomainsRecordsReverse::model()->getItemByAddress($address);

        return $item ? $item['domain'] : null;
    }
}
