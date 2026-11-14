<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoCore\Setapp;
use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\Markets\Versum;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

abstract class VersumHunter extends AbstractHunter
{
    protected const kMarketplaceKTID                = Setapp\Versum::MarketplaceKTID;

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        if ($tokensContract == Setapp\Versum::TokensKTID)
            return self::kMarketplaceKTID;

        if ($tokensContract == Setapp\Versum::TokensKTID_VesselsGen0)
            return self::kMarketplaceKTID;

        return null;
    }

    //------------------------------------------------------------------------------------------------------------------

    protected static function GetSupportedCollectionKTIDs(): array
    {
        return [
            Setapp\Versum::TokensKTID,                  // Versum Items (Main)
            Setapp\Versum::TokensKTID_VesselsGen0,      // Vessel GEN-0

            Setapp\Teia::TokensKTID,                    // OBJKTs
            Setapp\EightBidou_8rgb::TokensKTID,         // 8bidou
            Setapp\EightBidou_24mono::TokensKTID,       // 8bidou GIN-EN
            Setapp\EightBidou_24rgb::TokensKTID,        // 8bidou RHODOPSIN

            'KT1S9VbCtVZUgAG4Q3VArvY5stu96q4CiPHZ',     // Distributed Consciousness
            'KT1JYioHwutnTpYPTk5yRe9YSfCaFGWhd9MR',     // Verses / Distributed Consciousness

            'KT1SyPgtiXTaEfBuMZKviWGNHqVrBBEjvtfQ',     // GOGOs
            'KT1MsdyBSAMQwzvDH4jt2mxUKJvBSWZuPoRJ',     // Neonz
            'KT1LHHLso8zQWQWg1HUukajdxxbkGfNoHjh6',     // Tezzardz
            'KT1L7GvUxZH5tfa6cgZKnH6vpp2uVxnFVHKu',     // Ottez
            'KT1PNcZQkJXMQ2Mg92HG1kyrcu3auFX5pfd8',     // Ziggurats
        ];
    }

    protected function analyzePurchaseEvent(array $recInit, array $subRecords, ?string $eventDetails=null): bool
    {
        try
        {
            # Also used for the auction-withdraw event (when someone won the auction)

            //----------------------------------------------------------------------------------------------------------

            $recTransfer = self::ExtractRecordByEntrypoint($subRecords, 'transfer');

            if ($recTransfer == null)
                throw new Exception('Cannot find record [transfer]');

            //----------------------------------------------------------------------------------------------------------

            $recRoyaltiesXtz = self::ExtractRecordByEntrypoint($subRecords, 'pay_royalties_xtz');

            if ($recRoyaltiesXtz == null)
                throw new Exception('Cannot find record [pay_royalties_xtz]');

            $buyerAddress  = $recRoyaltiesXtz['parameter']['value']['buyer'];
            $sellerAddress = $recRoyaltiesXtz['parameter']['value']['seller'];

            //----------------------------------------------------------------------------------------------------------

            switch ($recInit['entrypoint'])
            {
                case 'collect_swap':
                    $amountSpent = $recInit['amount'];

                    // Part of the XTZ may be refunded to the buyer;
                    // subtract it from the amount spent
                    while ($recMoneyBack = self::ExtractRecordByTargetAddress($subRecords, $buyerAddress))
                    {
                        $amountSpent -= $recMoneyBack['amount'];
                    }
                    break;

                case 'withdraw':
                    $auctionId = $recInit['parameter']['value'] ?? null;

                    $auctionDetails = Versum::GetAuctionDetails($auctionId);

                    if ($auctionDetails === null)
                        throw new Exception("Cannot get auction details");

                    $amountSpent = $auctionDetails->highestBid;
                    break;

                case 'accept_offer':
                    $offerId = $recInit['parameter']['value'] ?? null;

                    $offerDetails = Versum::GetOfferDetails($offerId);

                    if ($offerDetails === null)
                        throw new Exception("Cannot get offer details");

                    $amountSpent = $offerDetails->price;
                    break;

                default:
                    throw new Exception('Unknown entrypoint ['.$recInit['entrypoint'].'] to detect amountSpent');
            }

            //----------------------------------------------------------------------------------------------------------

            $recBatchFwdXtz = self::ExtractRecordByEntrypoint($subRecords, 'batch_fwd_xtz');

            if ($recBatchFwdXtz == null)
                throw new Exception('Cannot find record [batch_fwd_xtz]');

            $amountRevenue = 0;

            $royaltyAddressList = [];
            $royaltyTotalAmount = 0;

            foreach ($recBatchFwdXtz['parameter']['value']['receivers'] as $item)
            {
                if ($item['address'] == $sellerAddress)
                {
                    $amountRevenue = $item['amount'];
                    continue; // not a royalty: this is the seller
                }

                // Why += ? Because sometimes one wallet may appear more than one time!
                $royaltyAddressList[$item['address']] = ($royaltyAddressList[$item['address']] ?? 0) + $item['amount'];
                $royaltyTotalAmount += $item['amount'];
            }

            # $amountRevenue may be 0 on Versum: 100% royalty sent to another address (not the creator)

            //----------------------------------------------------------------------------------------------------------

            $transfers = $this->unpackTransfersEvents($recTransfer);

            if (empty($transfers))
                throw new Exception('Cannot unpack transfer data');

            if (count($transfers) > 1)
                throw new Exception('Only a single transfer is supported');

            //----------------------------------------------------------------------------------------------------------

            $transfer = $transfers[0];

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenPurchase;
            $event->event_details           = $eventDetails;

            $event->sender_address          = $sellerAddress; # Notice: it cannot be $transfer->sender, because it'll be contract, not seller
            $event->target_address          = $transfer->target;
            $event->royalty_address         = null;
            $event->royalty_address_list    = $royaltyAddressList;

            $event->revenue                 = $amountRevenue;
            $event->royalty                 = $royaltyTotalAmount;
            $event->price                   = $amountSpent;

            $event->currency                = TezosNetwork::kCURRENCY_ID;
            $event->quantity                = $transfer->quantity;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = $transfer->tokenAddress;
            $event->token_id                = $transfer->tokenId;

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
