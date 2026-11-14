<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoCore\Setapp;

abstract class FxHashV2Hunter extends AbstractHunter
{
    protected const kMarketplaceKTID                = Setapp\FxHash_v2::MarketplaceKTID;
    protected const kMarketplaceFeeWallet           = Setapp\FxHash_v2::MarketplaceFeeWallet;

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        switch($tokensContract)
        {
            case Setapp\FxHash_v1::TokensKTID:      return Setapp\FxHash_v1::MarketplaceKTID;
            case Setapp\FxHash_v2::TokensKTID:      return Setapp\FxHash_v2::MarketplaceKTID;

            // This is important for New-Mint notifications
            case Setapp\FxHash_v1::IssuerKTID:      return Setapp\FxHash_v1::IssuerKTID;
            case Setapp\FxHash_v2::IssuerKTID:      return Setapp\FxHash_v2::IssuerKTID;
        }

        return null;
    }
}
