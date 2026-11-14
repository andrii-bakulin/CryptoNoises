<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Networks\Tezos\Markets\Versum;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use CryptoCore\Time;
use Exception;

class VersumHunter_Main extends VersumHunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        // Direct purchase
        if ($recInit['entrypoint'] == 'collect_swap')
        {
            if ($this->analyzeFreePurchaseEvent($recInit, $subRecords))
                return true;

            return $this->analyzePurchaseEvent($recInit, $subRecords);
        }

        if ($recInit['entrypoint'] == 'create_swap')
            return $this->analyzeListEvent($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'cancel_swap')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    protected function analyzeFreePurchaseEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenFreePurchaseBlock($recInit, $subRecords, function($initParams): ?TokenActions\Swap
        {
            $swapId = $initParams['value']['swap_id'] ?? null;

            if ($swapId === null)
                return null;

            return Versum::GetSwapDetails($swapId);
        });
    }

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenListEvent($recInit, $subRecords, function(array $recInit, TokenActions\Transfer $transfer)
        {
            $recValue = $recInit['parameter']['value'];

            if (!in_array($recValue['token']['address'], self::GetSupportedCollectionKTIDs()))
                throw new Exception("Unsupported collection contract ".$recValue['token']['address']);

            if ($recValue['currency'] !== null)
                throw new Exception('Currency is not null');

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = $transfer->tokenAddress;  # $recValue['token']['address'];
            $listing->tokenId           = $transfer->tokenId;       # $recValue['token']['nat'];

            $listing->price             = $recValue['starting_price_in_nat'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = $recValue['token_amount'];

            $listing->creator           = null;
            $listing->royalty_d4        = null;

            if ($recValue['ending_time'])
            {
                $listing->duration      = strtotime($recValue['ending_time']) - Time::Time();
                $listing->burnOnEnd     = (bool) $recValue['burn_on_end'];
            }

            return $listing;
        });
    }
}
