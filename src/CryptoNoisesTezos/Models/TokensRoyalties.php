<?php

namespace CryptoNoisesTezos\Models;

use CryptoNoisesTezos\Entities\TokenRoyalty;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensRoyalties extends VssModel
{
    use Tokens_Cleanup;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_royalties';
    }

    public function getRoyalty(int $contractAdid, int $tokenUnid, int $decimals): ?int
    {
        $sql = "
            SELECT SUM(shares * POWER(10, :base_decimals) / power(10, decimals)) 
            FROM {$this->tableName()}
            WHERE contract_adid = :contract_adid 
                AND token_unid = :token_unid
        ";

        return Vss::dbTezos()->queryScalar($sql, [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
            'base_decimals' => $decimals,
        ]);
    }

    /**
     * @param TokenRoyalty[] $records
     */
    public function saveRecords(int $contractAdid, int $tokenUnid, array $records): void
    {
        $this->cleanUp($contractAdid, $tokenUnid);

        foreach ($records as $record)
        {
            Vss::dbTezos()->insert($this->tableName(), [
                'contract_adid' => $contractAdid,
                'token_unid'    => $tokenUnid,
                'royalty_adid'  => $record->royaltyAdid,
                'shares'        => $record->shares,
                'decimals'      => $record->decimals,
            ], 'IGNORE');
        }
    }
}
