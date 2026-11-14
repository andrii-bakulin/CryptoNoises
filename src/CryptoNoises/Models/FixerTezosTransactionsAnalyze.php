<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;

class FixerTezosTransactionsAnalyze extends FixerAbstract
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'fixer_tezos_transactions_analyze';
    }

    public function getZombieLevels($secondsBack): array
    {
        $sql = "
            SELECT level
            FROM {$this->tableName()}
            WHERE (state = :state_processing OR (state = :state_error AND notice = :notice_no_data))
              AND ts_scan_started < NOW() - INTERVAL :timeout SECOND
            ORDER BY level
        ";
        return Vss::db()->queryColumn($sql, [
            'state_processing' => self::kSTATE_Processing,
            'state_error'      => self::kSTATE_Error,
            'notice_no_data'   => \CryptoNoisesTezos\Syncs\LevelSyncInterface::kERROR_NoData,
            'timeout'          => $secondsBack,
        ]);
    }
}
