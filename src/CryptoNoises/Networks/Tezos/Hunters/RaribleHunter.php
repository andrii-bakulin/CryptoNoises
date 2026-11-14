<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Enum;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class RaribleHunter extends AbstractHunter
{
    public const kTokensKTID                        = 'KT18pVpRXKPY2c4U2yFEGSH3ZnhB2kL8kwXS';   # Rarible Contract

    public const kMarketplaceKTID                   = 'KT198mqFKkiWerXLmMCw69YB1i6yzYtmGVrC';   # Rarible Marketplace

    public const kMarketplaceFeeWallet              = 'tz1gkQ4rNzPTgf1Yn3oBweWHAAvvF9VJv9hh';

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

    //------------------------------------------------------------------------------------------------------------------

    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        if ($recInit['entrypoint'] == 'match_orders')
            return $this->analyzeDirectPurchaseEvent($recInit, $subRecords);

        // Mark the transaction as handled without emitting an event
        if ($recInit['entrypoint'] == 'cancel')
            return true;

        // List events are not detected: after a token is removed from sale, putting it on sale again
        // may leave no transaction on chain, so listings cannot be tracked reliably.

        return false;
    }

    protected function analyzeDirectPurchaseEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $recTransfer = self::ExtractRecordByEntrypoint($subRecords, 'transfer');

            if ($recTransfer == null)
                throw new Exception('Cannot find transfer-transaction');

            //----------------------------------------------------------------------------------------------------------
            // 2: Prepare vars

            $transfers = $this->unpackTransfersEvents($recTransfer);

            if (empty($transfers))
                throw new Exception('Cannot detect transfers records');

            if (count($transfers) > 1)
                throw new Exception('Too many transfer-records. Current logic supports only one!');

            $transfer = $transfers[0];

            //----------------------------------------------------------------------------------------------------------
            // Classify every sub-record payout by its target
            // 1) find

            $royaltyAddress = null;

            $amountRevenue  = 0;
            $amountRoyalty  = 0;
            $amountStoreFee = 0;

            foreach ($subRecords as $record)
            {
                if ($record['entrypoint'] !== null || $record['amount'] == 0)
                    continue;

                switch ($record['target']) # payout address
                {
                    case $transfer->sender: # Seller
                        $amountRevenue += $record['amount'];
                        break;

                    case $transfer->target: # Buyer
                        // Sometimes 1 XTZ is returned to the buyer: ignore it
                        break;

                    case self::kMarketplaceFeeWallet:
                        $amountStoreFee += $record['amount'];
                        break;

                    default:
                        $royaltyAddress = $record['target'];
                        $amountRoyalty += $record['amount'];
                        break;
                }
            }

            if ($amountRevenue == 0)
                throw new Exception('Revenue is zero');

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenPurchase;
            $event->sender_address          = $transfer->sender;
            $event->target_address          = $transfer->target;
            $event->royalty_address         = $royaltyAddress;
            $event->fee_address             = $amountStoreFee > 0 ? self::kMarketplaceFeeWallet : null;

            $event->revenue                 = $amountRevenue  > 0 ? $amountRevenue  : null;
            $event->royalty                 = $amountRoyalty  > 0 ? $amountRoyalty  : null;
            $event->fee                     = $amountStoreFee > 0 ? $amountStoreFee : null;
            $event->price                   = $event->revenue + $event->royalty + $event->fee;

            $event->currency                = TezosNetwork::kCURRENCY_ID;
            $event->quantity                = $transfer->quantity;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = $transfer->tokenAddress;
            $event->token_id                = $transfer->tokenId;

            $this->parser->pushEvent($event);

            //----------------------------------------------------------------------------------------------------------

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }
}
