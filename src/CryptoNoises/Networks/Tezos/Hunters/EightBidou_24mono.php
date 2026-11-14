<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoCore\Setapp;

class EightBidou_24mono extends EightBidou
{
    protected const kTokensKTID                     = Setapp\EightBidou_24mono::TokensKTID;
    protected const kMarketplaceKTID                = Setapp\EightBidou_24mono::MarketplaceKTID;
    protected const kMarketplaceFeeWallet           = Setapp\EightBidou_24mono::MarketplaceFeeWallet;

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
