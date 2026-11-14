<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

// Primary sales - Tezotopia
// Primary sales contract for Tezotopia NFTs.
class Tezotopia_Marketplace extends Tezotopia
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        // listings ('list_tokens') are intentionally not handled

        if ($recInit['entrypoint'] == 'delist')
            return $this->delistTokens($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'buy')
            return $this->buy($recInit, $subRecords);

        return false;
    }

    private function delistTokens(array $recInit, array $subRecords): bool
    {
        return $this->fixEventAsSkipped($recInit, $subRecords);
    }

    private function buy(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet);
    }
}
