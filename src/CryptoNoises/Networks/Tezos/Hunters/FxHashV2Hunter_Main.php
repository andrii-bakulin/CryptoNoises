<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoCore\Setapp;
use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\Markets;
use CryptoNoises\Networks\Tezos\TezosHelper;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class FxHashV2Hunter_Main extends FxHashV2Hunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        $entrypoint = $recInit['entrypoint'];

        if ($entrypoint == 'listing_accept')
        {
            if ($this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet))
                return true;

            return $this->analyzeFreePurchaseEvent($recInit, $subRecords);
        }

        if ($entrypoint == 'listing')
            return $this->analyzeListEvent($recInit, $subRecords);

        if ($entrypoint == 'listing_cancel')
            return $this->analyzeUnlistEvent($recInit, $subRecords);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Offer on Token

        if ($entrypoint == 'offer')
            return $this->analyzeTokenOfferMakeEvent($recInit, $subRecords);

        if ($entrypoint == 'offer_accept')
            return $this->analyzeTokenOfferAcceptEvent($recInit, $subRecords);

        if ($entrypoint == 'offer_cancel')
            return $this->analyzeTokenOfferCancelEvent($recInit, $subRecords);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Offer on Collection

        if ($entrypoint == 'collection_offer')
            return $this->analyzeCollectionOfferMakeEvent($recInit, $subRecords);

        if ($entrypoint == 'collection_offer_accept')
            return $this->analyzeCollectionOfferAcceptEvent($recInit, $subRecords);

        if ($entrypoint == 'collection_offer_cancel')
            return $this->analyzeCollectionOfferCancelEvent($recInit, $subRecords);

        return false;
    }

    private function analyzeFreePurchaseEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenFreePurchaseBlock($recInit, $subRecords, function($initParams): ?TokenActions\Swap
        {
            $swapId = $initParams['value'] ?? null;

            if ($swapId === null)
                return null;

            return Markets\FxHashV2::GetSwapDetails($swapId);
        });
    }

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenListEvent($recInit, $subRecords, function(array $recInit, TokenActions\Transfer $transfer)
        {
            $valInit = $recInit['parameter']['value'];

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = $transfer->tokenAddress;
            $listing->tokenId           = $transfer->tokenId;

            $listing->price             = $valInit['price'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = 1;

            $listing->creator           = null;
            $listing->royalty_d4        = null;

            return $listing;
        });
    }

    private function analyzeUnlistEvent(array $recInit, array $subRecords): bool
    {
        return $this->fixEventAsSkipped($recInit, $subRecords);
    }

    //==================================================================================================================

    private function analyzeTokenOfferMakeEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $recValue = $recInit['parameter']['value'];

            $offer = new TokenActions\Offer();
            $offer->tokenAddress = Setapp\FxHash::GetTokensKTIDByVersion($recValue['gentk']['version']);

            if ($offer->tokenAddress === null)
                throw new Exception("Unknown store-version #{$recValue['gentk']['version']}");

            $offer->tokenId      = (string) $recValue['gentk']['id'];
            $offer->tokenAmount  = 1;
            $offer->creator      = null;
            $offer->buyer        = $recInit['sender'];
            $offer->price        = $recValue['price'];

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenOffer;
            $event->sender_address          = $offer->buyer;
            $event->offer_owners_addresses  = TezosHelper::GetTokenHolders($offer->tokenAddress, $offer->tokenId);
            $event->royalty_address         = $offer->creator;

            $event->price                   = $offer->price;
            $event->currency                = TezosNetwork::kCURRENCY_ID;
            $event->quantity                = $offer->tokenAmount;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = $offer->tokenAddress;
            $event->token_id                = $offer->tokenId;

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

    private function analyzeTokenOfferAcceptEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet, Enum\EventFlowDetails::OfferAccepted);
    }

    private function analyzeTokenOfferCancelEvent(array $recInit, array $subRecords): bool
    {
        return $this->fixEventAsSkipped($recInit, $subRecords);
    }

    //==================================================================================================================

    private function analyzeCollectionOfferMakeEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $recValue = $recInit['parameter']['value'];

            if (!isset($recValue['collection']))
                throw new Exception('collectionId is missing');

            $offer = new TokenActions\CollectionOffer();
            $offer->collectionAddress   = Setapp\FxHash::GetTokensKTIDByIssuerTokenId($recValue['collection']);
            $offer->collectionId        = $recValue['collection'];
            $offer->tokenAmount         = $recValue['amount'];

            $offer->creator             = null;
            $offer->buyer               = $recInit['sender'];
            $offer->price               = $recValue['price'];

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::CollectionOffer;
            $event->sender_address          = $offer->buyer;
            $event->offer_owners_addresses  = TezosHelper::GetCollectionHolders($offer->collectionAddress, $offer->collectionId);
            $event->royalty_address         = $offer->creator;

            $event->price                   = $offer->price;
            $event->currency                = TezosNetwork::kCURRENCY_ID;
            $event->quantity                = $offer->tokenAmount;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = Setapp\FxHash::GetIssuersKTIDByIssuerTokenId($recValue['collection']);
            $event->token_id                = $offer->collectionId;         // Issuer token-id

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

    private function analyzeCollectionOfferAcceptEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet, Enum\EventFlowDetails::OfferAccepted);
    }

    private function analyzeCollectionOfferCancelEvent(array $recInit, array $subRecords): bool
    {
        return $this->fixEventAsSkipped($recInit, $subRecords);
    }
}
