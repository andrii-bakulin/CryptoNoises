<?php

namespace CryptoNoisesTezos\Models\Log;

use CryptoCore\Vss\Vss;

/**
 * Raw log tables split by block ranges: `<prefix><digits><x...>`, e.g. `bigmap_updates_13xxxxx` = levels
 * 1300000..1399999, `transactions_1417xxxx` = levels 14170000..14179999. Ranges have different sizes
 * (see the models' tableName()), so a table is dropped only when its WHOLE range is below the cleanup level.
 */
final class LevelRangeTables
{
    /**
     * @return array{0: int, 1: int}|null [first level, last level] of the table, null if the name does not match
     */
    public static function GetLevelRange(string $tableName, string $prefix): ?array
    {
        if (!preg_match('/^'.preg_quote($prefix, '/').'(\d+)(x+)$/', $tableName, $m))
            return null;

        $size = 10 ** strlen($m[2]);
        $from = (int) $m[1] * $size;

        return [$from, $from + $size - 1];
    }

    /**
     * Drops every `<prefix>...` table of the Tezos log DB whose last level is < $level.
     *
     * @return string[] dropped tables
     */
    public static function DropTablesBefore(string $prefix, int $level): array
    {
        $dropped = [];
        $tables = Vss::dbTezosLog()->query("SHOW TABLES LIKE '".addcslashes($prefix, '_%')."%'");

        foreach ($tables as $row)
        {
            $tableName = (string) reset($row);
            $range = self::GetLevelRange($tableName, $prefix);

            if ($range === null || $range[1] >= $level)
                continue;

            Vss::dbTezosLog()->query("DROP TABLE IF EXISTS `$tableName`");
            $dropped[] = $tableName;
        }

        return $dropped;
    }
}
