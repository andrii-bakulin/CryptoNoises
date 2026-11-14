<?php

namespace CryptoNoises\Models;

use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Temp_EventsFlowLockedIds extends VssModel
{
    private static ?string $TimePoint = null;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        if (self::$TimePoint === null)
            self::$TimePoint = Time::NowByMask('Ymd_His');

        return 'temp__events_flow_locked_ids__'.self::$TimePoint;
    }

    public function begin(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
              `event_flow_id` int(10) unsigned NOT NULL,
              PRIMARY KEY (`event_flow_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ";
        Vss::db()->query($sql);

        $sql = "
            TRUNCATE TABLE `{$this->tableName()}`
        ";
        Vss::db()->query($sql);
    }

    public function fixLockedIds(string $platform): void
    {
        $sql = "
            INSERT IGNORE INTO `{$this->tableName()}`
            SELECT event_flow_id
            FROM ".EventsNotifications::model()->tableName($platform)."
            WHERE notify_status = :status_idle
        ";
        Vss::db()->query($sql, ['status_idle' => EventsNotifications::kNotifyStatus_Idle]);
    }

    public function close(): void
    {
        $sql = "
            DROP TABLE IF EXISTS `{$this->tableName()}`
        ";
        Vss::db()->query($sql);
    }
}
