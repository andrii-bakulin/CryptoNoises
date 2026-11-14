<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\CustomData;
use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\Markets;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class AkaSwap_Auction extends AkaSwap
{
    // direct_price value that means "no direct (buy-now) price"
    private const kNO_DIRECT_PRICE = 10000000000000000;

    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kAuctionKTID)
            return false;

        if ($recInit['entrypoint'] == 'make_auction')
            return $this->create($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'cancel_auction') # manually or finish-without-winner
            return $this->cancel($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'close_auction')  # finish-with-winner
            return $this->analyzePurchaseEvent($recInit, $subRecords, Enum\EventFlowDetails::AuctionConclude);

        if ($recInit['entrypoint'] == 'direct_purchase')
            return $this->analyzePurchaseEvent($recInit, $subRecords, Enum\EventFlowDetails::AuctionConclude);

        if ($recInit['entrypoint'] == 'bid')
            return $this->bid($recInit, $subRecords);

        return false;
    }

    private function create(array $recInit, array $subRecords): bool
    {
        try
        {
            $valueAuction = $recInit['parameter']['value'];

            if (!self::IsSupportedTokensContract($valueAuction['token_fa2']))
                throw new Exception("Unknown token-address #{$valueAuction['token_fa2']}");

            if ($valueAuction['start_price'] == 0)
                throw new Exception("XTZ price is ZERO");

            //----------------------------------------------------------------------------------------------------------

            $transfer = $this->unpackTransfersEvent($subRecords);

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::AuctionStart;
            $event->sender_address          = $transfer->sender;
            $event->target_address          = $transfer->target;
            $event->royalty_address         = null;

            $event->price                   = $valueAuction['start_price'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;
            $event->quantity                = $transfer->quantity;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = $transfer->tokenAddress;
            $event->token_id                = $transfer->tokenId;

            $event->custom_data             = [
                CustomData::kDURATION               => $valueAuction['auction_duration'] * 3600,
                CustomData::kAUCTION_DIRECT_PRICE   => $valueAuction['direct_price'] != self::kNO_DIRECT_PRICE ? $valueAuction['direct_price'] : null,
                CustomData::kAUCTION_BID_INC_PCT    => $valueAuction['raise_percentage'],
            ];

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}", $recInit);
            return false;
        }
    }

    private function cancel(array $recInit, array $subRecords): bool
    {
        try
        {
            $auctionId = $recInit['parameter']['value'] ?? null;

            if (!is_numeric($auctionId))
                throw new Exception("AuctionId is not defined");

            $customData = [];
            $customData[CustomData::kAUCTION_ID] = $auctionId;

            //----------------------------------------------------------------------------------------------------------

            $transfer = $this->unpackTransfersEvent($subRecords);

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::AuctionCancel;
            $event->sender_address          = $transfer->sender;
            $event->target_address          = $transfer->target;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = $transfer->tokenAddress;
            $event->token_id                = $transfer->tokenId;

            $event->custom_data             = $customData;

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}", $recInit);
            return false;
        }
    }

    protected function bid(array $recInit, array $subRecords): bool
    {
        try
        {
            $auctionId = $recInit['parameter']['value'] ?? null;

            if (!is_numeric($auctionId))
                throw new Exception("AuctionId is not defined");

            $customData = [];
            $customData[CustomData::kAUCTION_ID] = $auctionId;

            $auctionDetails = Markets\AkaSwap::GetAuctionDetails($auctionId);

            if ($auctionDetails === null)
                throw new Exception("Cannot get auction details");

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::AuctionBid;
            $event->sender_address          = $recInit['sender'];
            $event->target_address          = $recInit['target'];
            $event->royalty_address         = $auctionDetails->creator;

            $event->price                   = $recInit['amount'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = $auctionDetails->tokenAddress;
            $event->token_id                = $auctionDetails->tokenId;

            $event->custom_data             = $customData;

            $this->parser->pushEvent($event);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            if (count($subRecords) == 1)
            {
                $customData[CustomData::kAUCTION_TOP_BID]   = $event->price;
                $customData[CustomData::kAUCTION_TOP_OWNER] = $event->sender_address;

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                $record = array_pop($subRecords);

                $event = new EventFlow();
                $event->initInTezosNetworkByRecord($record);

                $event->event_type              = Enum\EventFlowType::AuctionOutBid;
                $event->sender_address          = $record['sender'];
                $event->target_address          = $record['target'];

                $event->price                   = $record['amount'];
                $event->currency                = TezosNetwork::kCURRENCY_ID;

                $event->marketplace_address     = $recInit['target'];
                $event->token_address           = $auctionDetails->tokenAddress;
                $event->token_id                = $auctionDetails->tokenId;

                $event->custom_data             = $customData;

                $this->parser->pushEvent($event);
            }

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }
}
