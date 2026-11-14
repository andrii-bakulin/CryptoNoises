<?php

namespace CryptoNoises\Models\Log;

use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class EventsFlowExecAt extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(string $timePoint): string
    {
        return 'log_events_flow_exec_at_'.date('Y_m', strtotime($timePoint));
    }

    //------------------------------------------------------------------------------------------------------------------

    public function prepareTable(int $secondsAhead): void
    {
        MonthlyTables::Prepare('template__log_events_flow_exec_at', function (string $timePoint): string
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

    public function save(array $item)
    {
        return Vss::dbLog()->insertAndGetId($this->tableName(Time::Now()), $item);
    }
}