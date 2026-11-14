<?php

namespace CryptoNoises\Core;

use CryptoNoises\Config;

class URL
{
    public static function InnerRedirectToToken(array $marketUrlItem, string $tokenAddress, string $tokenId): ?string
    {
        $url = rtrim(Config::cfg()->publicDomain, '/').$marketUrlItem['internal_url_suffix'];

        $url = str_replace('{$MARKET_URL_ID$}', $marketUrlItem['market_url_id'], $url);
        $url = str_replace('{$TOKEN_ADDRESS$}', $tokenAddress, $url);
        $url = str_replace('{$TOKEN_ID$}',      $tokenId, $url);
        return $url;
    }

    public static function InnerRedirectToUser(array $marketUrlItem, string $address): ?string
    {
        $url = rtrim(Config::cfg()->publicDomain, '/').$marketUrlItem['internal_url_suffix'];

        $url = str_replace('{$MARKET_URL_ID$}', $marketUrlItem['market_url_id'], $url);
        $url = str_replace('{$ADDRESS$}',       $address, $url);
        return $url;
    }

    public static function InnerRedirectToContract(array $marketUrlItem, string $address): ?string
    {
        $url = rtrim(Config::cfg()->publicDomain, '/').$marketUrlItem['internal_url_suffix'];

        $url = str_replace('{$MARKET_URL_ID$}', $marketUrlItem['market_url_id'], $url);
        $url = str_replace('{$ADDRESS$}',       $address, $url);
        return $url;
    }

    public static function InnerRedirectToCollection(array $marketUrlItem, string $collectionAddress,
                                                     ?string $collectionSubGroup = null, ?int $collectionSubId = null): ?string
    {
        $url = rtrim(Config::cfg()->publicDomain, '/').$marketUrlItem['internal_url_suffix'];

        $url = str_replace('{$MARKET_URL_ID$}',         $marketUrlItem['market_url_id'], $url);
        $url = str_replace('{$COLLECTION_ADDRESS$}',    $collectionAddress,    $url);
        $url = str_replace('{$COLLECTION_SUB_GROUP$}',  (string) $collectionSubGroup,   $url);
        $url = str_replace('{$COLLECTION_SUB_ID$}',     (string) $collectionSubId,      $url);
        return $url;
    }
}
