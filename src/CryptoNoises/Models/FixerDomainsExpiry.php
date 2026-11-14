<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class FixerDomainsExpiry extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'fixer_domains_expiry';
    }

    public function fix(string $domain, int $hoursScope): bool
    {
        return (bool) Vss::db()->insert($this->tableName(), [
            'domain'      => $domain,
            'hours_scope' => $hoursScope,
        ], 'IGNORE');
    }

    public function cleanup(): void
    {
        Vss::db()->query("
            DELETE FROM {$this->tableName()}
            WHERE ts_event < NOW() - INTERVAL 33 DAY
        ");
    }
}
