<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class ProfilesTzprofilesContracts extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'profiles_tzprofiles_contracts';
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function getItem(?int $contractAdid): ?array
    {
        if ($contractAdid == null)
            return null;

        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['contract_adid' => $contractAdid]);
    }

    public function isContractExists(?int $contractAdid): bool
    {
        return $this->getItem($contractAdid) != null;
    }

    public function create(int $contractAdid, string $contractAddress, int $profileAdid, string $profileAddress): array
    {
        Vss::dbTezos()->insert($this->tableName(), [
            'contract_adid'     => $contractAdid,
            'contract_address'  => $contractAddress,
            'profile_adid'      => $profileAdid,
            'profile_address'   => $profileAddress,
        ]);

        return $this->getItem($contractAdid);
    }
}
