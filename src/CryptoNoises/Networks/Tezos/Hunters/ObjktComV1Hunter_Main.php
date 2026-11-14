<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Networks\Tezos\TezosNetwork;
use CryptoNoises\Entities\TokenActions;

class ObjktComV1Hunter_Main extends ObjktComV1Hunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        $entrypoint = $recInit['entrypoint'];

        // List
        if ($entrypoint == 'ask')
            return $this->analyzeListEvent($recInit, $subRecords);

        // Unlist
        if ($entrypoint == 'retract_ask')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        // Direct Purchase
        if ($entrypoint == 'fulfill_ask')
            return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet);

        return false;
    }

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenListEvent($recInit, $subRecords, function(array $recInit, TokenActions\Transfer $transfer)
        {
            $recValue = $recInit['parameter']['value'];

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = $transfer->tokenAddress;  # $recValue['fa2'];
            $listing->tokenId           = $transfer->tokenId;       # $recValue['objkt_id'];

            $listing->price             = $recValue['price'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = $recValue['amount'];

            $listing->creator           = $recValue['artist'];
            $listing->royalty_d4        = $recValue['royalties'] * 10; // Incoming value for 25% is '250' => need convert to 2500

            return $listing;
        });
    }
}
