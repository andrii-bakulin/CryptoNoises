<?php

namespace CryptoNoises\GatewayTezos;

use CryptoNoises\Entities\UserDomain;
class Domains
{
    /**
     * @param string $address
     * @return UserDomain[]
     */
    public static function GetUserDomainsByOwner(string $address): array
    {
        $result = [];

        $domains = \CryptoNoises\GatewayTezos\Models\DomainsRecords::model()->selectDomainsByOwner($address);

        foreach ($domains as $domain)
        {
            $tsExpired = \CryptoNoises\GatewayTezos\Models\DomainsExpiry::model()->getExpireDateForDomain($domain);

            if ($tsExpired == null)
                continue;

            $result[] = new UserDomain($domain, $address, $tsExpired);
        }

        return $result;
    }

    public static function GetDomainOwner(string $domain): ?string
    {
        $item = \CryptoNoises\GatewayTezos\Models\DomainsRecords::model()->getItemByDomain($domain);

        return $item ? $item['owner'] : null;
    }
}
