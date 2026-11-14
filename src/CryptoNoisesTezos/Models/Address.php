<?php

namespace CryptoNoisesTezos\Models;

use CryptoNoisesTezos\Core\Log;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Address extends VssModel
{
    const TYPE_Wallet                           = 'wallet';
    const TYPE_Contract                         = 'contract';
    const TYPE_Undefined                        = null;

    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'address';
    }

    public function getItemByAdid(int $adid): ?array
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE adid = :adid";

        return Vss::dbTezos()->queryRow($sql, ['adid'=>$adid]);
    }

    public function getItemByAddress(string $address): ?array
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE address = :address";

        return Vss::dbTezos()->queryRow($sql, ['address'=>$address]);
    }

    public function create(string $address)
    {
        $type = self::TYPE_Undefined;

        if (strlen($address) > 36) // longer than any Tezos address
        {
            Log::Fatal('Model-Address', 'create', "Address value too long", [
                'address' => $address,
            ]);

            $notice  = $address;
            $address = substr($address, 0, 36);
        }
        else
        {
            if (preg_match('/^tz[1-9]/u', $address) > 0)
            {
                $type = self::TYPE_Wallet;
            }
            if (preg_match('/^KT1/u', $address) > 0)
            {
                $type = self::TYPE_Contract;
            }

            $notice = null;
        }

        $insert = [
            'address_type'      => $type,
            'address'           => $address,
            'address_ci'        => $address,
            'notice'            => $notice,
        ];

        return Vss::dbTezos()->insert($this->tableName(), $insert, 'IGNORE');
    }
}
