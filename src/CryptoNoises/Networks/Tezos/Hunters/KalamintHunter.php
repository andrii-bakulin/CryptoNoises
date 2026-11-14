<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

abstract class KalamintHunter extends AbstractHunter
{
    public const kTokensKTID                        = 'KT1EpGgjQs73QfFJs9z7m1Mxm5MTnpC2tqse';   # Kalamint Art House

    public const kMarketplaceKTID                   = 'KT1EpGgjQs73QfFJs9z7m1Mxm5MTnpC2tqse';   # Kalamint Art House    (same contract as the token contract)
    public const kAuctionFactoryKTID                = 'KT1MiSxkVDFDrAMYCZZXdBEkNrf1NWzfnnRR';   # Kalamint Auction Factory
    public const kOffersKTID                        = 'KT1VVNTAxmYjSbxvduQu4k4V2AeNn4y4EzZK';   # Kalamint Art House | Offers catcher

    public const kMarketplaceFeeWallet              = 'tz1ijy2Z7sSmmxPqsZ6iGRn1ZHrCtpwR7XNm';   # Kalamint Trading Fee Collector

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID, self::kAuctionFactoryKTID, self::kOffersKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        if ($tokensContract == self::kTokensKTID)
            return self::kMarketplaceKTID;

        return null;
    }
}
