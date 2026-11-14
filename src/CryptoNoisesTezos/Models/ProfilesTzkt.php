<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class ProfilesTzkt extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'profiles_tzkt';
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function getItem(?int $profileAdid): ?array
    {
        if ($profileAdid == null)
            return null;

        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['profile_adid' => $profileAdid]);
    }

    public function getSecondsSinceLastUpdate(?int $profileAdid): ?int
    {
        if ($profileAdid == null)
            return null;

        return Vss::dbTezos()->queryScalar("
            SELECT TIMESTAMPDIFF(SECOND, COALESCE(ts_updated, ts_created), NOW()) as seconds 
            FROM {$this->tableName()}
            WHERE profile_adid = :profile_adid
        ", ['profile_adid' => $profileAdid]);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function create(int $profileAdid, string $profileAddress): array
    {
        Vss::dbTezos()->insert($this->tableName(), [
            'profile_adid'      => $profileAdid,
            'profile_address'   => $profileAddress,
        ], 'IGNORE');

        return $this->getItem($profileAdid);
    }

    public function save(int $profileAdid, array $data): bool
    {
        return (bool) Vss::dbTezos()->update($this->tableName(), $data, ['profile_adid' => $profileAdid]);
    }
}
