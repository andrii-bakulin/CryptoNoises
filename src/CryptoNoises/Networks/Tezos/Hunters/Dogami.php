<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

abstract class Dogami extends AbstractHunter
{
    public const kTokensKTID                        = 'KT1NVvPsNDChrLRH5K2cy6Sc9r1uuUwdiZQd';

    public const kMarketplaceKTID                   = 'KT1QcZ81mxhYutPCA7WeVAd7Ko1etWJBuRqQ';
    public const kDogaKTID                          = 'KT1Ha4yFVeyzw6KRAdkzq6TxDHB97KG4pZe8';

    public const kAuctionKTID                       = 'KT1NVXxerB7NFi12CGP79fwoLHhuaxPBV2tQ';
    public const kCrowdsaleKTID                     = 'KT1HTDtMBRCKoNHjfWEEvXneGQpCfPAt6BRe';
    public const kNameUpdaterKTID                   = 'KT1BSWXPDtdWVEKLcHJicUot647RyMPZ1rBa';
    public const kStakingKTID                       = 'KT1EF89rHUm71YoFqUTnc4DjiPUas9HyGWb1';
    public const kCrowdsaleV3KTID                   = 'KT1TjHyHTnL4VMQQyD75pr3ZTemyPvQxRPpA';

    public const kMarketplaceFeeWallet              = 'tz1bj9NxKYups7WCFmytkYJTw6rxtizJR79K';

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID, self::kDogaKTID, self::kAuctionKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        if ($tokensContract == self::kTokensKTID)
            return self::kMarketplaceKTID;

        return null;
    }
}
