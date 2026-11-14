<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;
use CryptoCore\Vss\VssDbExp;

class AddressByCreators extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'address_by_creators';
    }

    public function getAddressByCreatorName(string $creatorName): ?string
    {
        $address = Vss::dbTezos()->selectScalar('address', $this->tableName(), ['creator_name' => $creatorName]);

        if ($address)
        {
            Vss::dbTezos()->update($this->tableName(), ['hits' => VssDbExp::query('hits+1')], ['creator_name' => $creatorName]);
        }

        return $address;
    }
}
