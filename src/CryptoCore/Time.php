<?php

namespace CryptoCore;

class Time
{
    public static function Now($offset=0): string
    {
        return date('Y-m-d H:i:s', self::Time($offset));
    }

    /**
     * @param string $mask date() format, e.g. 'Ymd_His'
     */
    public static function NowByMask(string $mask, int $offset = 0): string
    {
        return date($mask, self::Time($offset));
    }

    public static function Time($offset=0): int
    {
        return time() + $offset;
    }

    public static function Microtime(): float
    {
        return microtime(true);
    }

    public static function NormalizeTimestamp(?string $timestamp): ?string
    {
        if ($timestamp == null)
            return null;

        $time = strtotime($timestamp);

        if ($time === false)
            return null;

        // Far-future dates (MySQL TIMESTAMP ends in 2038) are treated as "no date"
        if ((int) date('Y', $time) > 2030)
            return null;

        // '2021-12-22T04:54:56Z' -> '2021-12-22 04:54:56'
        return date('Y-m-d H:i:s', $time);
    }
}
