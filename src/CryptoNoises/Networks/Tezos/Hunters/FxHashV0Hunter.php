<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoCore\Setapp;

abstract class FxHashV0Hunter extends AbstractHunter
{
    protected const kTokensKTID                     = Setapp\FxHash_v0::TokensKTID;

    protected const kMarketplaceKTID                = Setapp\FxHash_v0::MarketplaceKTID;

    protected const kMarketplaceFeeWallet           = Setapp\FxHash_v0::MarketplaceFeeWallet;

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        if ($tokensContract == self::kTokensKTID)
            return self::kMarketplaceKTID;

        return null;
    }
}
