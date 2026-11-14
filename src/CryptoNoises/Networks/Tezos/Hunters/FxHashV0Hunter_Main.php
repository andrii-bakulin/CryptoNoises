<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Networks\Tezos\Markets;
use CryptoNoises\Networks\Tezos\TezosNetwork;

class FxHashV0Hunter_Main extends FxHashV0Hunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        if ($recInit['entrypoint'] == 'collect')
        {
            if ($this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet))
                return true;

            return $this->analyzeFreePurchaseEvent($recInit, $subRecords);
        }

        if ($recInit['entrypoint'] == 'offer')
            return $this->analyzeListEvent($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'cancel_offer')
            return $this->analyzeUnlistEvent($recInit, $subRecords);

        return false;
    }

    private function analyzeFreePurchaseEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenFreePurchaseBlock($recInit, $subRecords, function($initParams): ?TokenActions\Swap
        {
            $swapId = $initParams['value'] ?? null;

            if ($swapId === null)
                return null;

            return Markets\FxHashV0::GetSwapDetails($swapId);
        });
    }

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        // User put item on sale (on marketplace)

        return $this->analyzeStdTokenListEvent($recInit, $subRecords, function(array $recInit, TokenActions\Transfer $transfer)
        {
            $recValue = $recInit['parameter']['value'];

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = $transfer->tokenAddress;
            $listing->tokenId           = $transfer->tokenId;

            $listing->price             = $recValue['price'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = 1;

            $listing->creator           = $recValue['creator'];
            $listing->royalty_d4        = $recValue['royalties'] * 10; // Incoming value for 25% is '250' => need convert to 2500

            return $listing;
        });
    }

    private function analyzeUnlistEvent(array $recInit, array $subRecords): bool
    {
        // User remove item from sale (on marketplace)

        return $this->fixEventAsSkipped($recInit, $subRecords);
    }
}
