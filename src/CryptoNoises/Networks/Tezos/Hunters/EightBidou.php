<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Networks\Tezos\TezosNetwork;

abstract class EightBidou extends AbstractHunter
{
    protected function parseTransactionForMarketplace(string $marketplaceKTID, string $marketplaceFeeWallet, array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != $marketplaceKTID)
            return false;

        if ($recInit['entrypoint'] == 'buy')
            return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, $marketplaceFeeWallet);

        if ($recInit['entrypoint'] == 'swap')
            return $this->analyzeListEvent($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'cancelswap')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenListEvent($recInit, $subRecords, function(array $recInit, TokenActions\Transfer $transfer)
        {
            $recValue = $recInit['parameter']['value'];

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = $transfer->tokenAddress;  # $recValue['nft_contract_address'];
            $listing->tokenId           = $transfer->tokenId;       # $recValue['nft_id'];

            $listing->price             = $recValue['payment'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = $recValue['nft_amount'];

            $listing->creator           = $recValue['creator'];
            $listing->royalty_d4        = $recValue['royalties'] * 100; // Incoming value for 10% is '10' => need convert to 1000

            return $listing;
        });
    }
}
