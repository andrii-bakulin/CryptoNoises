<?php

namespace CryptoNoises\Models\Log;

use CryptoCore\Time;
use CryptoCore\Vss\Vss;

/**
 * Monthly log tables (`<prefix>_YYYY_MM`) are created from a fixed template table (`template__<prefix>`, see sql/schema/main.sql),
 * so it works on a fresh database where no month table exists yet.
 */
final class MonthlyTables
{
    /**
     * Creates the current month table and the table for (now + $secondsAhead), if they do not exist.
     *
     * @param callable $tableNameByTimePoint function (string $timePoint): string
     */
    public static function Prepare(string $templateTable, callable $tableNameByTimePoint, int $secondsAhead): void
    {
        foreach ([Time::Now(), Time::Now($secondsAhead)] as $timePoint)
        {
            $tableName = $tableNameByTimePoint($timePoint);

            Vss::dbLog()->query("CREATE TABLE IF NOT EXISTS `$tableName` LIKE `$templateTable`");
        }
    }
}
