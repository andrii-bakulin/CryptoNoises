<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Bigmaps extends VssModel
{
    const kTYPE_Basic                           = 'basic';
    const kTYPE_Ledger                          = 'ledger';
    const kTYPE_LedgerLight                     = 'ledger-light';
    const kTYPE_MarketDeals                     = 'market-deals';
    const kTYPE_MarketOffers                    = 'market-offers';
    const kTYPE_Metadata                        = 'metadata';
    const kTYPE_Operators                       = 'operators';
    const kTYPE_Supply                          = 'supply';
    const kTYPE_TokenMetadata                   = 'token-metadata';

    const kSTATE_Active                         = 'active';
    const kSTATE_Removed                        = 'removed';

    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'bigmaps';
    }

    public function getItem(int $bigmap): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['bigmap'=>$bigmap]);
    }

    public function getItemByType(string $contract, string $type): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), [
            'contract'  => $contract,
            'type'      => $type,
            'state'     => self::kSTATE_Active,
        ]);
    }

    public function getContractByBigmap(int $bigmap): ?string
    {
        return Vss::dbTezos()->selectScalar('contract', $this->tableName(), ['bigmap'=>$bigmap]);
    }

    public function insert(array $item)
    {
        return Vss::dbTezos()->insert($this->tableName(), $item);
    }

    public function update(int $bigmap, array $item): void
    {
        Vss::dbTezos()->update($this->tableName(), $item, ['bigmap'=>$bigmap]);
    }

    public function remove(int $bigmap, int $level): void
    {
        Vss::dbTezos()->update($this->tableName(),
            ['state' => self::kSTATE_Removed, 'last_level' => $level],
            ['bigmap' => $bigmap]);
    }
}
