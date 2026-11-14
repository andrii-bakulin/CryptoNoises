<?php

namespace CryptoNoises\Models\Log;

use CryptoNoises\Config;
use CryptoNoises\Models\EventsFlow;
use CryptoNoises\Models\Temp_EventsFlowLockedIds;
use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class EventsFlowLog extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(string $timePoint): string
    {
        return 'events_flow_'.date('Y_m', strtotime($timePoint));
    }

    //------------------------------------------------------------------------------------------------------------------

    public function prepareTable(int $secondsAhead): void
    {
        MonthlyTables::Prepare('template__events_flow', function (string $timePoint): string
        {
            return $this->tableName($timePoint);
        }, $secondsAhead);
    }

    public function dropTable(int $secondsOffset): void
    {
        $tableName = $this->tableName(Time::Now($secondsOffset));
        Vss::dbLog()->query("DROP TABLE IF EXISTS $tableName");
    }

    //------------------------------------------------------------------------------------------------------------------

    public function moveData(string $timePoint): void
    {
        $tableName = $this->tableName($timePoint);

        $sqlMain = "
            FROM ".Config::cfg()->dbMain.".".EventsFlow::model()->tableName()." AS a
            LEFT JOIN ".Config::cfg()->dbMain.".".Temp_EventsFlowLockedIds::model()->tableName()." AS b
                   ON a.event_flow_id = b.event_flow_id
            WHERE a.ts_event < :time_point
              AND b.event_flow_id IS NULL
        ";

        // not replicated (log database)
        Vss::dbLog()->query("INSERT IGNORE INTO $tableName SELECT a.* ".$sqlMain, ['time_point' => $timePoint]);

        // replicated to the replica
        Vss::db()->query("DELETE a ".$sqlMain, ['time_point' => $timePoint]);
    }
}
