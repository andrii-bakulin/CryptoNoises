<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\Markets\Kalamint;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class KalamintHunter_Main extends KalamintHunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        if ($recInit['entrypoint'] == 'buy')
            return $this->analyzeDirectPurchaseEvent($recInit, $subRecords);

        // Mark the transaction as handled without emitting an event
        if ($recInit['entrypoint'] == 'mint')
            return true;

        if ($recInit['entrypoint'] == 'list_token')
            return $this->analyzeListEvent($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'delist_token')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    protected function analyzeDirectPurchaseEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            if (count($subRecords) == 3)
            {
                self::SortRecordsByAmountDesc($subRecords);

                list ($recRevenue, $recRoyalty, $recStore) = $subRecords;
            }
            else if (count($subRecords) == 2)
            {
                self::SortRecordsByAmountDesc($subRecords);

                list ($recRevenue, $recStore) = $subRecords;
                $recRoyalty = null;
            }
            else
            {
                throw new Exception('SubRecords count is not 2 or 3');
            }

            //----------------------------------------------------------------------------------------------------------
            // 2: Prepare vars

            if (!isset($recInit['parameter']['value']))
                throw new Exception('Cannot detect token_id');

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenPurchase;
            $event->sender_address          = $recRevenue['target'];
            $event->target_address          = $recInit['sender'];
            $event->royalty_address         = $recRoyalty ? $recRoyalty['target'] : null;
            $event->fee_address             = $recStore['target'];

            $event->price                   = $recRevenue['amount'] + $recStore['amount'] + ($recRoyalty ? $recRoyalty['amount'] : 0);
            $event->revenue                 = $recRevenue['amount'];
            $event->royalty                 = $recRoyalty ? $recRoyalty['amount'] : null;
            $event->fee                     = $recStore['amount'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = self::kTokensKTID;
            $event->token_id                = $recInit['parameter']['value'];

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

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $listing = new TokenActions\Listing();
            $listing->tokenAddress  = self::kTokensKTID;
            $listing->tokenId       = $recInit['parameter']['value']['token_id'];

            $tokenData = Kalamint::GetTokenDetails($listing->tokenAddress, $listing->tokenId);

            if ($tokenData == null)
                throw new Exception("TokenData is empty for token=$listing->tokenAddress:$listing->tokenId");

            $listing->price             = $recInit['parameter']['value']['price'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = 1;

            $listing->creator           = $tokenData['creator'];
            $listing->royalty_d4        = $tokenData['creator_royalty'] * 100; // Incoming value for 25% is '25' => need convert to 2500

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenList;
            $event->sender_address          = $recInit['sender'];
            $event->target_address          = $recInit['target'];
            $event->royalty_address         = $listing->creator;

            $event->price                   = $listing->price;
            $event->currency                = $listing->currency;
            $event->quantity                = $listing->quantity;

            $event->royalty_d4              = $listing->royalty_d4;

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = $listing->tokenAddress;
            $event->token_id                = $listing->tokenId;

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }
}
