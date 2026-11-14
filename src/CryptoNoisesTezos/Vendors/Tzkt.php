<?php

namespace CryptoNoisesTezos\Vendors;

use CryptoCore\Http\AbstractVendor;

class Tzkt extends AbstractVendor
{
    // TzKT returns at most this many items per request
    const kMAX_ITEMS_PER_REQUEST = 10000;

    public static function GetAccountDetails(string $contract): ?array
    {
            $result = self::GetDataJson("https://api.tzkt.io/v1/accounts/$contract");

        return $result->isSuccess() ? $result->data : null;
    }

    public static function GetAccountMetadata(string $contract): ?array
    {
        $result = self::GetDataJson("https://api.tzkt.io/v1/accounts/$contract/metadata");

        return $result->isSuccess() ? $result->data : null;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function GetBlockLastIndex(): ?int
    {
        $result = self::GetDataJson("https://api.tzkt.io/v1/blocks/count");

        return $result->isSuccess() ? (int)($result->data)-1 : null;
    }

    public static function GetBlockDetails(int $level): ?array
    {
        $result = self::GetDataJson("https://api.tzkt.io/v1/blocks/{$level}?operations=true");

        return $result->isSuccess() ? $result->data : null;
    }

    public static function GetBlockBigmapUpdates(int $level): ?array
    {
        $result = self::GetDataJson("https://api.tzkt.io/v1/bigmaps/updates?level=$level&limit=".self::kMAX_ITEMS_PER_REQUEST);

        return $result->isSuccess() ? $result->data : null;
    }

    /**
     * One page of a list endpoint ("operations/transactions", "bigmaps/updates") for levels [$levelFrom .. $levelTo],
     * sorted by id; $idAfter continues after the last item of the previous page.
     */
    public static function GetLevelRangePage(string $path, int $levelFrom, int $levelTo, int $limit, ?int $idAfter = null): ?array
    {
        $url = "https://api.tzkt.io/v1/$path?level.ge=$levelFrom&level.le=$levelTo&sort.asc=id&limit=$limit"
            .($idAfter !== null ? "&id.gt=$idAfter" : '');

        $result = self::GetDataJson($url);

        return $result->isSuccess() && is_array($result->data) ? $result->data : null;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function GetBigmapDetails(int $bigmap): ?array
    {
        $result = self::GetDataJson("https://api.tzkt.io/v1/bigmaps/$bigmap");

        return $result->isSuccess() ? $result->data : null;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function GetContractStorageData(string $contract): ?array
    {
        $result = self::GetDataJson("https://api.tzkt.io/v1/contracts/$contract/storage/");

        return $result->isSuccess() ? $result->data : null;
    }
}
