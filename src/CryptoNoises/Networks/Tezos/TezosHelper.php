<?php

namespace CryptoNoises\Networks\Tezos;

use CryptoNoises\Config;
use CryptoNoises\Entities\Marketplace;
use CryptoNoises\Models;

class TezosHelper
{
    /**
     * @param string $tokenAddress
     * @param string $tokenId
     * @return array|null - list of wallet addresses
     */
    public static function GetTokenHolders(string $tokenAddress, string $tokenId): ?array
    {
        if (Models\Exceptions::IsRequiredToIgnoreTokenOffers($tokenAddress, $tokenId))
            return null;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $activeHolders = \CryptoNoises\GatewayTezos\Models\BigmapDataLedgers::GetActiveHoldersWalletsForToken($tokenAddress, $tokenId);

        if (empty($activeHolders))
            return null;

        if (count($activeHolders) >= Config::kMaxActiveHoldersForOffer)
        {
            Models\Exceptions::MarkTokenOffersIgnored($tokenAddress, $tokenId, "AUTOFIX: Too many active-holders (".count($activeHolders).")");
            return null;
        }

        return $activeHolders;
    }

    /**
     * @param string $tokenAddress
     * @param string $collectionId
     * @return array|null - list of wallet addresses
     */
    public static function GetCollectionHolders(string $tokenAddress, string $collectionId): ?array
    {
        if (Models\Exceptions::IsRequiredToIgnoreCollectionOffers($tokenAddress, $collectionId))
            return null;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $activeHolders = \CryptoNoises\GatewayTezos\Models\BigmapDataLedgers::GetActiveHoldersWalletsForCollection($tokenAddress, $collectionId);

        if (empty($activeHolders))
            return null;

        if (count($activeHolders) >= Config::kMaxActiveHoldersForCollection)
        {
            Models\Exceptions::MarkCollectionOffersIgnored($tokenAddress, $collectionId, "AUTOFIX: Too many active-holders (".count($activeHolders).")");
            return null;
        }

        return $activeHolders;
    }

    public static function GetTokenCurrentEditions(string $tokenAddress, string $tokenId): ?int
    {
        return \CryptoNoises\GatewayTezos\Models\BigmapDataLedgers::GetTokenCurrentEditions($tokenAddress, $tokenId);
    }

    public static function GetMarketplace(?string $marketplaceAddress, ?string $collectionAddress): ?Marketplace
    {
        if (empty($marketplaceAddress))
            return null;

        $marketplaceItem = Models\Shared\Marketplaces::model()->getItemByContractId($marketplaceAddress, $collectionAddress);

        if ($marketplaceItem === null)
        {
            $marketplaceItem = Models\Shared\Marketplaces::model()->autoCreateMarketplace($marketplaceAddress);

            if ($marketplaceItem === null)
                return null;
        }

        return Marketplace::InitByItem($marketplaceItem);
    }
}
