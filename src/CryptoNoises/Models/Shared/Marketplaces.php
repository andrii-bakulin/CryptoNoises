<?php

namespace CryptoNoises\Models\Shared;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;
use CryptoCore\Vss\VssDbExp;

class Marketplaces extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'marketplaces';
    }

    public function getItemByContractId(string $marketplaceAddress, ?string $collectionAddress): ?array
    {
        if ($collectionAddress)
        {
            $sql = "
                SELECT *
                FROM {$this->tableName()}
                WHERE marketplace_address = :marketplace_address
                   AND collection_address = :collection_address
            ";
            $item = Vss::dbShared()->queryRow($sql, [
                'marketplace_address' => $marketplaceAddress,
                'collection_address'  => $collectionAddress,
            ]);

            if (!empty($item))
            {
                $this->fixhit($marketplaceAddress, $collectionAddress);
                return $item;
            }
        }

        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE marketplace_address = :marketplace_address
              AND collection_address  = ''
        ";
        $item = Vss::dbShared()->queryRow($sql, [
            'marketplace_address' => $marketplaceAddress,
        ]);

        if (!empty($item))
            $this->fixhit($marketplaceAddress, '');

        return $item;
    }

    public function autoCreateMarketplace(string $marketplaceAddress): ?array
    {
        Vss::dbShared()->insert($this->tableName(), [
            'marketplace_address' => $marketplaceAddress,
            'collection_address'  => '',
            'marketplace_name'    => null,
            'create_type'         => 'auto',
        ], 'IGNORE');

        return $this->getItemByContractId($marketplaceAddress, '');
    }

    private function fixhit(string $marketplaceAddress, ?string $collectionAddress): void
    {
        Vss::dbShared()->update($this->tableName(), ['hits' => VssDbExp::query('hits+1')], [
            'marketplace_address' => $marketplaceAddress,
            'collection_address'  => $collectionAddress,
        ]);
    }
}
