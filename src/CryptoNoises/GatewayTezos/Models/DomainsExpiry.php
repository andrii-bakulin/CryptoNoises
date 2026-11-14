<?php

namespace CryptoNoises\GatewayTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class DomainsExpiry extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return \CryptoNoisesTezos\Models\DomainsExpiry::model()->tableName();
    }

    public function getExpireDateForDomain(string $domain): ?string
    {
        return Vss::dbTezos()->selectScalar('ts_expired', $this->tableName(), ['domain' => $domain]);
    }

    public function getDomainsExpiringBetween(int $secondsBack, int $secondsAhead): array
    {
        $secondsBack    = abs($secondsBack);
        $secondsAhead = abs($secondsAhead);

        $sql = "
            SELECT * 
              FROM {$this->tableName()}
             WHERE ts_expired >= NOW() - INTERVAL :seconds_back SECOND
               AND ts_expired <= NOW() + INTERVAL :seconds_front SECOND
        ";
        return Vss::dbTezos()->query($sql, [
            'seconds_back'  => $secondsBack,
            'seconds_front' => $secondsAhead,
        ]);
    }
}
