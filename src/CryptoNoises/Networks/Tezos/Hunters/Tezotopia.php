<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

abstract class Tezotopia extends AbstractHunter
{
    public const kTokensKTID                        = 'KT1ViVwoVfGSCsDaxjwoovejm1aYSGz7s2TZ';

    public const kMarketplaceKTID                   = 'KT1Wkv9KR9jsnp1LLquw9RYtranmB4nCim37';

    public const kMarketplaceFeeWallet              = 'KT1BJmsK1zhLy5PZNgC7YDfdjciAVTw1A5gq';

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
