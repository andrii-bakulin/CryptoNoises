<?php

namespace CryptoNoises\Helpers;

use Exception;

class Helper
{
    public static function Uniqid(int $length = 16): string
    {
        if (function_exists("random_bytes"))
        {
            $bytes = random_bytes(ceil($length / 2));
        }
        elseif (function_exists("openssl_random_pseudo_bytes"))
        {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        }
        else
        {
            throw new Exception("no cryptographically secure random function available");
        }

        return substr(bin2hex($bytes), 0, $length);
    }

    /**
     * With $limit > 0 the result always has exactly $limit items (missing ones are null),
     * so it is safe to use with list($a, $b) = ...
     */
    public static function SplitBySpaces(string $message, int $limit = -1): array
    {
        $parts = preg_split("/[\s]+/", $message, $limit);

        return $limit > 0 ? array_pad($parts, $limit, null) : $parts;
    }

    public static function UnpackIndexesList(string $message, int $maxIndex): array
    {
        $message = trim($message);

        $result = [];

        if (preg_match('/^[, 0-9-]+$/iu', $message) == false)
            return $result;

        $idxBlocks = preg_split('/[, ]/iu', $message);

        // Guard against abusive input
        if (count($idxBlocks) > 100)
            return [];

        foreach ($idxBlocks as $idxBlock)
        {
            $idxBlock = trim($idxBlock);

            if ($idxBlock == '')
                continue;

            list ($index0, $index1) = array_pad(explode('-', $idxBlock, 2), 2, null);

            $index0 = (int) $index0;
            $index1 = (int) $index1;

            if ($index1 == 0)
                $index1 = $index0;

            if ($index0 <= 0 || $index0 > $maxIndex)
                continue;

            if ($index1 <= 0 || $index1 > $maxIndex)
                continue;

            for ($i=$index0; $i<=$index1; $i++)
                $result[] = $i;
        }

        $result = array_unique($result);
        sort($result);
        return $result;
    }

    public static function TrimRightZerosInNumber($number): string
    {
        if (strpos($number, '.') === false)
            return $number;

        $number = rtrim($number, '0');
        $number = rtrim($number, '.');

        return $number;
    }

    public static function NumberToKeycapEmoji($number): string
    {
        $digitsMaps = [
            '0' => '0️⃣',
            '1' => '1️⃣',
            '2' => '2️⃣',
            '3' => '3️⃣',
            '4' => '4️⃣',
            '5' => '5️⃣',
            '6' => '6️⃣',
            '7' => '7️⃣',
            '8' => '8️⃣',
            '9' => '9️⃣',
        ];

        $digits = str_split((string)$number);
        $number = '';

        foreach ($digits as $digit)
        {
            $number .= $digitsMaps[$digit] ?? $digit;
        }

        return $number;
    }
}
