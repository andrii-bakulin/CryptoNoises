<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Networks\Tezos\Markets\HicEtNunc;
use CryptoNoises\Networks\Tezos\TezosNetwork;

class HicEtNuncHunter extends AbstractHunter
{
    public const kTokensKTID                        = 'KT1RJ6PbjHpwc3M5rw5s2Nbmefwbuwbdxton';   # hic et nunc NFTs

    // hic et nunc Minter (not used here): 'KT1Hkg5qeNhfwpKW4fXvq7HGZB9z2EnmCCA9'
    public const kMarketplaceKTID                   = 'KT1HbQepzV1nVGg8QVznG7z4RcHseD5kwqBn';   # hic et nunc Marketplace

    public const kMarketplaceFeeWallet              = 'tz1UBZUkXpKGhYsP5KtzDNqLLchwF4uHrGjw';

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        return null;
    }

    //------------------------------------------------------------------------------------------------------------------

    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        if ($recInit['entrypoint'] == 'collect')
        {
            if ($this->analyzeFreePurchaseEvent($recInit, $subRecords))
                return true;

            return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet);
        }

        if ($recInit['entrypoint'] == 'swap')
            return $this->analyzeListEvent($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'cancel_swap')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    /**
     * @param array $recInit
     * @param array $subRecords
     * @return bool
     */
    private function analyzeFreePurchaseEvent(array $recInit, array $subRecords): bool
    {
        // If user set price "0.00" then on free-purchase will be called "collect" action, but only with 2 records:
        // 1) entrypoint = 'collect'
        // 2) entrypoint = 'transfer'
        // So need to fix this record as "free-transfer" item

        return $this->analyzeStdTokenFreePurchaseBlock($recInit, $subRecords, function($initParams): ?TokenActions\Swap
        {
            $swapId = $initParams['value'] ?? null;

            if ($swapId === null)
                return null;

            return HicEtNunc::GetSwapDetails($swapId);
        });
    }

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenListEvent($recInit, $subRecords, function(array $recInit, TokenActions\Transfer $transfer)
        {
            $recValue = $recInit['parameter']['value'];

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = $transfer->tokenAddress;
            $listing->tokenId           = $transfer->tokenId;

            $listing->price             = $recValue['xtz_per_objkt'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = $recValue['objkt_amount'];

            $listing->creator           = $recValue['creator'];
            $listing->royalty_d4        = $recValue['royalties'] * 10; // Incoming value for 25% is '250' => need convert to 2500

            return $listing;
        });
    }
}
