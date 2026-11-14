<?php

namespace CryptoCore\Models;

use CryptoCore\Vss\VssDb;
use CryptoCore\Vss\VssModel;

/**
 * Journal of notable sync / analyze events (`fixer_events` table).
 */
abstract class FixerEventsAbstract extends VssModel
{
    abstract protected function dbConnection(): VssDb;

    public function tableName(): string
    {
        return 'fixer_events';
    }

    public function fix(string $scope, string $action, ?int $value = null): void
    {
        $this->dbConnection()->insert($this->tableName(), [
            'scope'     => $scope,
            'action'    => $action,
            'value'     => $value,
        ]);
    }
}
