<?php

namespace CryptoCore\Models;

use CryptoCore\Vss\VssDb;
use CryptoCore\Vss\VssModel;

/**
 * Key-value settings stored in the `config` table (command locks, last analyzed levels, etc.).
 */
abstract class ConfigAbstract extends VssModel
{
    abstract protected function dbConnection(): VssDb;

    public function tableName(): string
    {
        return 'config';
    }

    public function getValue(string $key, ?string $default = null): ?string
    {
        $item = $this->dbConnection()->queryRow("SELECT * FROM {$this->tableName()} WHERE cfg_key = :cfg_key", ['cfg_key' => $key]);

        return $item ? $item['cfg_value'] : $default;
    }

    public function setValue(string $key, ?string $value): void
    {
        $item = ['cfg_key' => $key, 'cfg_value' => $value];
        $this->dbConnection()->insertOrUpdate($this->tableName(), $item, $item);
    }
}
