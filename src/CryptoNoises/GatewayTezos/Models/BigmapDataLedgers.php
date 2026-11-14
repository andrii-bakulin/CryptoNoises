<?php

namespace CryptoNoises\GatewayTezos\Models;

use CryptoNoises\Networks\Tezos\TezosNetwork;
use CryptoNoisesTezos\Models;
use CryptoCore\Vss\Vss;

class BigmapDataLedgers
{
    public static function GetActiveHoldersWalletsForToken(string $tokenAddress, string $tokenId): ?array
    {
        $exkeyTokenAddress = \CryptoNoisesTezos\Core\Bigmap::KeyToExkey($tokenAddress);
        $exkeyTokenId      = \CryptoNoisesTezos\Core\Bigmap::KeyToExkey($tokenId);

        $exkeyAddressBurn  = \CryptoNoisesTezos\Core\Bigmap::KeyToExkey(TezosNetwork::kADDRESS_BURN);

        //--------------------------------------------------------------------------------------------------------------

        $sql = "
            SELECT DISTINCT a.address
            FROM ".Models\BigmapKeysAddress::model()->tableName()." AS a
            JOIN ".Models\BigmapDataLedger::model()->tableName()." AS l
                ON a.exkey_id = -l.exkey_address
                AND l.exkey_token_address = :exkey_token_address
                AND l.exkey_token_id = :exkey_token_id
                AND l.state = :state_active
                AND l.value_num > 0
                AND l.exkey_address <> :exkey_address_burn
            WHERE a.address LIKE 'tz%'
        ";

        $holders = Vss::dbTezos()->queryColumn($sql, [
            'exkey_token_address'   => $exkeyTokenAddress,
            'exkey_token_id'        => $exkeyTokenId,
            'state_active'          => Models\BigmapDataAbstract::kSTATE_Active,
            'exkey_address_burn'    => $exkeyAddressBurn,
        ]);

        //--------------------------------------------------------------------------------------------------------------

        if (empty($holders))
        {
            $sql = "
                SELECT DISTINCT a.address
                FROM ".Models\BigmapKeysAddress::model()->tableName()." AS a
                JOIN ".Models\BigmapDataLedgerLight::model()->tableName()." AS l
                    ON a.exkey_id = -l.value
                    AND l.exkey_token_address = :exkey_token_address
                    AND l.exkey_token_id = :exkey_token_id
                    AND l.state = :state_active
                    AND l.value <> :exkey_address_burn
                WHERE a.address LIKE 'tz%'
            ";

            $holders = Vss::dbTezos()->queryColumn($sql, [
                'exkey_token_address'   => $exkeyTokenAddress,
                'exkey_token_id'        => $exkeyTokenId,
                'state_active'          => Models\BigmapDataAbstract::kSTATE_Active,
                'exkey_address_burn'    => $exkeyAddressBurn,
            ]);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Append owners which have active market-deals

        $sql = "
            SELECT DISTINCT a.address
            FROM ".Models\BigmapKeysAddress::model()->tableName()." AS a
            JOIN ".Models\BigmapDataMarketDeals::model()->tableName()." AS md
                ON a.exkey_id = -md.exkey_token_owner
                AND md.exkey_token_address = :exkey_token_address
                AND md.exkey_token_id = :exkey_token_id
                AND md.state = :state_active
                AND md.is_active = 'yes'
                AND (md.ts_active_until IS NULL OR md.ts_active_until > NOW())
            WHERE a.address LIKE 'tz%'
        ";

        $holdersOnMarkets = Vss::dbTezos()->queryColumn($sql, [
            'state_active'          => Models\BigmapDataAbstract::kSTATE_Active,
            'exkey_token_address'   => $exkeyTokenAddress,
            'exkey_token_id'        => $exkeyTokenId,
        ]);

        if (!empty($holdersOnMarkets))
        {
            $holders = array_merge($holders, $holdersOnMarkets);
            $holders = array_unique($holders);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $holders = array_filter($holders, function($holder)
        {
            return TezosNetwork::IsWalletAddressValid($holder);
        });

        return $holders ?: null;
    }

    /**
     * Works for fx(hash), where tokens_ref_collection.token_unid equals bigmap_data_*.exkey_token_id.
     * Not correct for token ids outside the signed BIGINT range.
     */
    public static function GetActiveHoldersWalletsForCollection(string $tokenAddress, string $collectionId): ?array
    {
        $exkeyTokenAddress = \CryptoNoisesTezos\Core\Bigmap::KeyToExkey($tokenAddress);
        $exkeyAddressBurn  = \CryptoNoisesTezos\Core\Bigmap::KeyToExkey(TezosNetwork::kADDRESS_BURN);

        $contractAdid      = \CryptoNoisesTezos\Core\TezosAddress::GetAdidByAddress($tokenAddress);

        if ($contractAdid == null)
            return null;

        //--------------------------------------------------------------------------------------------------------------
        // Heavy query: joins the ledger with tokens_ref_collection

        $sql = "
            SELECT DISTINCT a.address
            FROM ".Models\BigmapKeysAddress::model()->tableName()." AS a
            JOIN ".Models\BigmapDataLedger::model()->tableName()." AS l
                ON a.exkey_id = -l.exkey_address
                AND l.exkey_token_address = :exkey_token_address
                AND l.state = :state_active
                AND l.value_num > 0
                AND l.exkey_address <> :exkey_address_burn
                
            JOIN ".Models\TokensRefCollection::model()->tableName()." AS ref_col
                ON ref_col.token_unid = l.exkey_token_id
                AND ref_col.contract_adid = :contract_adid
                AND ref_col.collection_id = :collection_id
                
            WHERE a.address LIKE 'tz%'
        ";

        $holders = Vss::dbTezos()->queryColumn($sql, [
            'exkey_token_address'   => $exkeyTokenAddress,
            'state_active'          => Models\BigmapDataAbstract::kSTATE_Active,
            'exkey_address_burn'    => $exkeyAddressBurn,

            'contract_adid'         => $contractAdid,
            'collection_id'         => $collectionId,
        ]);

        //--------------------------------------------------------------------------------------------------------------

        if (empty($holders))
        {
            $sql = "
                SELECT DISTINCT a.address
                FROM ".Models\BigmapKeysAddress::model()->tableName()." AS a
                JOIN ".Models\BigmapDataLedgerLight::model()->tableName()." AS l
                    ON a.exkey_id = -l.value
                    AND l.exkey_token_address = :exkey_token_address
                    AND l.state = :state_active
                    AND l.value <> :exkey_address_burn

                JOIN ".Models\TokensRefCollection::model()->tableName()." AS ref_col
                    ON ref_col.token_unid = l.exkey_token_id
                    AND ref_col.contract_adid = :contract_adid
                    AND ref_col.collection_id = :collection_id

                WHERE a.address LIKE 'tz%'
            ";

            $holders = Vss::dbTezos()->queryColumn($sql, [
                'exkey_token_address'   => $exkeyTokenAddress,
                'state_active'          => Models\BigmapDataAbstract::kSTATE_Active,
                'exkey_address_burn'    => $exkeyAddressBurn,

                'contract_adid'         => $contractAdid,
                'collection_id'         => $collectionId,
            ]);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Append owners which have active market-deals

        $sql = "
            SELECT DISTINCT a.address
            FROM ".Models\BigmapKeysAddress::model()->tableName()." AS a
            JOIN ".Models\BigmapDataMarketDeals::model()->tableName()." AS md
                ON a.exkey_id = -md.exkey_token_owner
                AND md.exkey_token_address = :exkey_token_address
                AND md.state = :state_active
                AND md.is_active = 'yes'
                AND (md.ts_active_until IS NULL OR md.ts_active_until > NOW())

            JOIN ".Models\TokensRefCollection::model()->tableName()." AS ref_col
                ON ref_col.token_unid = md.exkey_token_id
                AND ref_col.contract_adid = :contract_adid
                AND ref_col.collection_id = :collection_id

            WHERE a.address LIKE 'tz%'
        ";

        $holdersOnMarkets = Vss::dbTezos()->queryColumn($sql, [
            'state_active'          => Models\BigmapDataAbstract::kSTATE_Active,
            'exkey_token_address'   => $exkeyTokenAddress,

            'contract_adid'         => $contractAdid,
            'collection_id'         => $collectionId,
        ]);

        if (!empty($holdersOnMarkets))
        {
            $holders = array_merge($holders, $holdersOnMarkets);
            $holders = array_unique($holders);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $holders = array_filter($holders, function($holder)
        {
            return TezosNetwork::IsWalletAddressValid($holder);
        });

        return $holders ?: null;
    }

    public static function GetTokenCurrentEditions(string $tokenAddress, string $tokenId): ?int
    {
        $exkeyTokenAddress = \CryptoNoisesTezos\Core\Bigmap::KeyToExkey($tokenAddress);
        $exkeyTokenId      = \CryptoNoisesTezos\Core\Bigmap::KeyToExkey($tokenId);

        $exkeyAddressBurn  = \CryptoNoisesTezos\Core\Bigmap::KeyToExkey(TezosNetwork::kADDRESS_BURN);

        //--------------------------------------------------------------------------------------------------------------

        $sql = "
            SELECT SUM(value_num)
            FROM ".Models\BigmapDataLedger::model()->tableName()."
            WHERE state = :state_active
                AND exkey_token_address = :exkey_token_address
                AND exkey_token_id = :exkey_token_id
                AND exkey_address <> :exkey_address_burn
        ";

        $editions = Vss::dbTezos()->queryScalar($sql, [
            'state_active'          => Models\BigmapDataAbstract::kSTATE_Active,
            'exkey_token_address'   => $exkeyTokenAddress,
            'exkey_token_id'        => $exkeyTokenId,
            'exkey_address_burn'    => $exkeyAddressBurn,
        ]);

        if ($editions > 0)
            // queryScalar returns a string; cast to int
            return (int) $editions;

        //--------------------------------------------------------------------------------------------------------------

        $sql = "
            SELECT COUNT(*)
            FROM ".Models\BigmapDataLedgerLight::model()->tableName()."
            WHERE state = :state_active
                AND exkey_token_address = :exkey_token_address
                AND exkey_token_id = :exkey_token_id
                AND value <> :exkey_address_burn
        ";

        $editions = Vss::dbTezos()->queryScalar($sql, [
            'state_active'          => Models\BigmapDataAbstract::kSTATE_Active,
            'exkey_token_address'   => $exkeyTokenAddress,
            'exkey_token_id'        => $exkeyTokenId,
            'exkey_address_burn'    => $exkeyAddressBurn,
        ]);

        if ($editions > 0)
            return $editions;

        //--------------------------------------------------------------------------------------------------------------

        return null;
    }
}
