<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Models;

class UserDomains
{
    /**
     * @var UserDomain[]
     */
    private array $records = [];

    public static function GetByUserId(int $userId): UserDomains
    {
        $instance = new UserDomains();
        $instance->records = [];

        $adids = Models\UsersRefWallets::model()->getWalletAdidsByUserId($userId);

        foreach ($adids as $adid)
        {
            $item = Models\Address::model()->getItemByAdid($adid);

            if (empty($item))
                continue;

            $instance->records = array_merge($instance->records,
                \CryptoNoises\GatewayTezos\Domains::GetUserDomainsByOwner($item['address']));
        }

        return $instance;
    }

    public static function GetUserIdsByTezosDomain(string $domain): array
    {
        $userIds = [];

        $ownerAddress = \CryptoNoises\GatewayTezos\Domains::GetDomainOwner($domain);

        if ($ownerAdid = Address::GetAdid($ownerAddress))
        {
            $userIds = array_merge($userIds,
                Models\UsersRefWallets::model()->getUserIdsByWalletAdid($ownerAdid));
        }

        return $userIds;
    }

    /**
     * @return UserDomain[]
     */
    public function getDomainsSortedByExpiration(): array
    {
        $items = $this->records;

        usort($items, function (UserDomain $i1, UserDomain $i2): int
        {
            return $i1->expireDuration <=> $i2->expireDuration;
        });

        return $items;
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return UserDomain[]
     */
    public function getItems(int $offset = 0, ?int $length = null): array
    {
        $items = $this->getDomainsSortedByExpiration();

        return array_slice($items, $offset, $length);
    }

    public function count(): int
    {
        return count($this->records);
    }
}
