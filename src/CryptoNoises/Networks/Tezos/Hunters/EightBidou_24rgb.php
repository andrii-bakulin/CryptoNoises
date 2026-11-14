<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoCore\Setapp;

class EightBidou_24rgb extends EightBidou
{
    protected const kTokensKTID                     = Setapp\EightBidou_24rgb::TokensKTID;
    protected const kMarketplaceKTID                = Setapp\EightBidou_24rgb::MarketplaceKTID;
    protected const kMarketplaceFeeWallet           = Setapp\EightBidou_24rgb::MarketplaceFeeWallet;

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

    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        return $this->parseTransactionForMarketplace(self::kMarketplaceKTID, self::kMarketplaceFeeWallet, $recInit, $subRecords);
    }
}
