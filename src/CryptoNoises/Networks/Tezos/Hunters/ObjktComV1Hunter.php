<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoCore\Setapp;

abstract class ObjktComV1Hunter extends AbstractHunter
{
    protected const kMarketplaceKTID                = Setapp\ObjktCom_v1::MarketplaceKTID;
    protected const kEnglishAuctionsKTID            = Setapp\ObjktCom_v1::EnglishAuctionsKTID;
    protected const kDutchAuctionsKTID              = Setapp\ObjktCom_v1::DutchAuctionsKTID;

    protected const kMarketplaceFeeWallet           = Setapp\ObjktCom_v1::MarketplaceFeeWallet;

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID, self::kEnglishAuctionsKTID, self::kDutchAuctionsKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        return null;
    }
}
