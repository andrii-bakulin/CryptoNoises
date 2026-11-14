<?php

namespace CryptoNoises\Models\Log;

use CryptoCore\Vss\Vss;
use VssCfg;

/**
 * Deletes old log rows and log files that hold personal data (Telegram updates, Discord payloads, user ids),
 * with the same retention as the monthly log tables.
 */
class PersonalDataRetention
{
    /**
     * @return array<string, int> deleted rows / files per source
     */
    public static function CleanUp(int $days): array
    {
        $params  = ['days' => $days];
        $deleted = [];

        foreach (['log_errors', 'log_events', 'log_discord_events'] as $table)
            $deleted[$table] = (int) Vss::dbLog()->query("DELETE FROM `$table` WHERE ts_event < NOW() - INTERVAL :days DAY", $params);

        $deleted['discord_missed_messages'] = (int) Vss::db()->query(
            "DELETE FROM discord_missed_messages WHERE ts_event < NOW() - INTERVAL :days DAY", $params);

        // members who left the project server
        $deleted['discord_users'] = (int) Vss::db()->query(
            "DELETE FROM discord_users WHERE ts_deactivated IS NOT NULL AND ts_deactivated < NOW() - INTERVAL :days DAY", $params);

        $deleted['log files'] = self::DeleteOldLogFiles($days);

        return $deleted;
    }

    private static function DeleteOldLogFiles(int $days): int
    {
        $count    = 0;
        $timeEdge = time() - $days * 86400;

        foreach (glob(VssCfg::cfg()->pathLog.'/*.log') ?: [] as $file)
        {
            if (is_file($file) && filemtime($file) < $timeEdge && unlink($file))
                $count++;
        }

        return $count;
    }
}
