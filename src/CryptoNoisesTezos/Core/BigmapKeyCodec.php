<?php

namespace CryptoNoisesTezos\Core;

use CryptoNoisesTezos\Models;

/**
 * Encodes bigmap keys (nat / address / any JSON value) into a single int "exkey" and back:
 *  - nat           -> the number itself (>= 0)
 *  - address       -> -id from BigmapKeysAddress
 *  - anything else -> -id from BigmapKeys (JSON with normalized key order; long values stored by sha1 stamp)
 */
final class BigmapKeyCodec
{
    // Exkey ids from this value up are rows of bigmap_keys_address, smaller ids are rows of bigmap_keys
    const kADDRESS_KEY_ID_MIN = 2000000000;

    private static array $BigmapExkeysCache = [];

    /**
     * @param mixed $keyValue
     * @return int
     */
    public static function KeyToExkey($keyValue): int
    {
        if (is_array($keyValue))
        {
            $keyValue = self::NormalizeKey($keyValue);
        }
        else if (Helper::IsNat($keyValue))
        {
            return $keyValue;
        }
        else if (TezosAddress::IsWalletOrContractAddress($keyValue))
        {
            $exkeyId = self::$BigmapExkeysCache[$keyValue] ?? null;

            if ($exkeyId === null)
            {
                $exkeyId = Models\BigmapKeysAddress::model()->getIdByValue($keyValue);

                if ($exkeyId === null)
                    $exkeyId = Models\BigmapKeysAddress::model()->insert($keyValue);

                self::$BigmapExkeysCache[$keyValue] = $exkeyId;
            }

            return -$exkeyId;
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $keyValue = json_encode($keyValue);
        $keyLength = mb_strlen($keyValue);

        if ($keyLength > 128+2)
        {
            $keyValueMain = '#ExKeyStamp#'.sha1($keyValue);
            $keyValueFull = $keyValue;

            $isAllowUseCache = false;
        }
        else
        {
            $keyValueMain = $keyValue;
            $keyValueFull = null;

            $isAllowUseCache = $keyLength <= 40;
        }

        $exkeyId = $isAllowUseCache ? self::$BigmapExkeysCache[$keyValueMain] ?? null : null;

        if ($exkeyId === null)
        {
            $exkeyId = Models\BigmapKeys::model()->getIdByValue($keyValueMain);

            if ($exkeyId === null)
                $exkeyId = Models\BigmapKeys::model()->insert($keyValueMain, $keyValueFull);

            if ($isAllowUseCache)
                self::$BigmapExkeysCache[$keyValueMain] = $exkeyId;
        }

        return -$exkeyId;
    }

    /**
     * @param int $exkey
     * @return mixed|null
     */
    public static function ExkeyToKey(int $exkey)
    {
        if ($exkey >= 0)
            return $exkey;

        $exkeyId = abs($exkey);

        if ($exkeyId >= self::kADDRESS_KEY_ID_MIN)
        {
            return Models\BigmapKeysAddress::model()->getValueById($exkeyId);
        }

        $item = Models\BigmapKeys::model()->getItemById($exkeyId);

        if ($item == null)
            return null;

        if (strpos($item['key'], '#ExKeyStamp#') === 0 && $item['key_extra'] !== null)
            return json_decode($item['key_extra'], true);

        return json_decode($item['key'], true);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    private static function NormalizeKey($key)
    {
        if (is_array($key))
        {
            ksort($key);
            foreach ($key as $k => $v)
                $key[$k] = self::NormalizeKey($v);
        }

        return $key;
    }
}
