<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TransactionsEntrypoints extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'transactions_entrypoints';
    }

    public function fix(string $address, string $entrypoint, int $level)
    {
        $sql = "
            INSERT INTO {$this->tableName()} 
                (target_address, entrypoint, level_min, level_max, calls_count)
            VALUES (:address, :entrypoint, :level, :level, 1)
            ON DUPLICATE KEY UPDATE 
                level_min = LEAST(level_min, :level),
                level_max = GREATEST(level_max, :level),
                calls_count = calls_count + 1;
        ";

        return Vss::dbTezos()->query($sql, ['address' => $address, 'entrypoint' => $entrypoint, 'level' => $level]);
    }
}
