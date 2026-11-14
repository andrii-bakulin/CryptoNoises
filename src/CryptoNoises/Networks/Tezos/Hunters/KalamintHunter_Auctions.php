<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\CustomData;
use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\Markets\Kalamint;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class KalamintHunter_Auctions extends KalamintHunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] == self::kAuctionFactoryKTID)
        {
            if ($recInit['entrypoint'] == 'create_auction')
                return $this->analyzeAuctionStartEvent($recInit, $subRecords);

            return false;
        }

        if ($recInit['target'] == self::kMarketplaceKTID)
        {
            if ($recInit['entrypoint'] == 'bid')
                return $this->analyzeAuctionBidEvent($recInit, $subRecords);

            if ($recInit['entrypoint'] == 'cancel_auction')
                return $this->analyzeAuctionCancelEvent($recInit, $subRecords);

            if ($recInit['entrypoint'] == 'resolve_auction')
                return $this->analyzeAuctionResolveEvent($recInit, $subRecords);

            return false;
        }

        return false;
    }

    //==================================================================================================================

    protected function analyzeAuctionStartEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $recValue = $recInit['parameter']['value'];

            $tokenAddress = self::kTokensKTID;
            $tokenId = $recValue['token_id'];

            if (!is_numeric($tokenId))
                throw new Exception('TokenId is not numeric');

            $tokenData = Kalamint::GetTokenDetails($tokenAddress, $tokenId);

            if ($tokenData == null)
                throw new Exception("TokenData is empty for token=$tokenAddress:$tokenId");

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::AuctionStart;
            $event->sender_address          = $recInit['sender'];
            $event->target_address          = $recInit['target'];
            $event->royalty_address         = $tokenData['owner'];

            $event->price                   = $recValue['reserve_price'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = $tokenAddress;
            $event->token_id                = $tokenId;

            $event->custom_data             = [
                CustomData::kDURATION => $recValue['round_time'],
            ];

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }

    protected function analyzeAuctionCancelEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $tokenAddress = self::kTokensKTID;
            $tokenId = $recInit['parameter']['value'];

            if (!is_numeric($tokenId))
                throw new Exception('TokenId is not numeric');

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::AuctionCancel;
            $event->sender_address          = $recInit['target']; # intentionally swapped: the contract is the sender
            $event->target_address          = $recInit['sender']; # intentionally swapped: the owner is the target

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = $tokenAddress;
            $event->token_id                = $tokenId;

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }

    //==================================================================================================================

    protected function analyzeAuctionBidEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $tokenAddress = self::kTokensKTID;
            $tokenId = $recInit['parameter']['value'];

            if (!is_numeric($tokenId))
                throw new Exception('TokenId is not numeric');

            $tokenData = Kalamint::GetTokenDetails($tokenAddress, $tokenId);

            if ($tokenData == null)
                throw new Exception("TokenData is empty for token=$tokenAddress:$tokenId");

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $recBid      = self::ExtractRecordByEntrypoint($subRecords, 'bid');
            $recStoreFee = self::ExtractRecordByTargetAddress($subRecords, self::kMarketplaceFeeWallet);

            if ($recBid == null)
                throw new Exception('Cannot find bid-record');

            if ($recStoreFee == null)
                throw new Exception('Cannot find store-fee-record for ['.self::kMarketplaceFeeWallet.']');

            if (count($subRecords) > 1)
                throw new Exception('More than one sub-record left; unexpected state');

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $customData = [];

            if (is_numeric($recInit['parameter']['value']))
            {
                $customData[CustomData::kAUCTION_ID] = $recInit['parameter']['value'];
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::AuctionBid;
            $event->sender_address          = $recInit['sender'];
            $event->target_address          = $recBid['target'];    // This is special separated smart-contract for this auction
            $event->royalty_address         = $tokenData['owner'];

            $event->price                   = $recBid['amount'];    // This value is 0.4% less than what the user sent
            $event->fee                     = $recStoreFee['amount'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = $tokenAddress;
            $event->token_id                = $tokenId;

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
                $event->royalty_address         = $tokenData['owner'];

                $event->price                   = $record['amount'];
                $event->currency                = TezosNetwork::kCURRENCY_ID;

                $event->marketplace_address     = self::kMarketplaceKTID;
                $event->token_address           = $tokenAddress;
                $event->token_id                = $tokenId;

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

    protected function analyzeAuctionResolveEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $tokenAddress = self::kTokensKTID;
            $tokenId = $recInit['parameter']['value'];

            if (!is_numeric($tokenId))
                throw new Exception('TokenId is not numeric');

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            self::ExtractRecordByEntrypoint($subRecords, 'resolve_auction'); // drop it from $subRecords
            $recEndAuction     = self::ExtractRecordByEntrypoint($subRecords, 'end_auction');

            if (count($subRecords) != 3)
                throw new Exception('SubRecords count is not 3');

            self::SortRecordsByAmountDesc($subRecords);

            list ($recRevenue, $recRoyalty, $recStore) = $subRecords;

            //----------------------------------------------------------------------------------------------------------
            // 2: Prepare vars

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenPurchase;
            $event->sender_address          = $recRevenue['target'];
            $event->target_address          = $recEndAuction['parameter']['value']['highest_bidder'];
            $event->royalty_address         = $recRoyalty['target'];
            $event->fee_address             = $recStore['target'];

            $event->price                   = $recRevenue['amount'] + $recRoyalty['amount'] + $recStore['amount'];
            $event->revenue                 = $recRevenue['amount'];
            $event->royalty                 = $recRoyalty['amount'];
            $event->fee                     = $recStore['amount'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = $tokenAddress;
            $event->token_id                = $tokenId;

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
