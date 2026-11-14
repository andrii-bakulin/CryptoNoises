<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

class DefaultIgnore extends AbstractHunter
{
    private static array $GlobalIgnoreEntryPoints = [
        'update_operators',
        'update_whitelist',
        'enter_lottery',
    ];

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        return null;
    }

    //------------------------------------------------------------------------------------------------------------------

    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        // Mark the transaction as handled without emitting an event
        if (in_array($recInit['entrypoint'], self::$GlobalIgnoreEntryPoints))
            return true;

        return false;
    }
}
