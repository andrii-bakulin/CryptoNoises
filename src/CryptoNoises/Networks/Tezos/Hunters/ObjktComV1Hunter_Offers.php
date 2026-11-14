<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosHelper;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class ObjktComV1Hunter_Offers extends ObjktComV1Hunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        $entrypoint = $recInit['entrypoint'];

        // Make Offer
        if ($entrypoint == 'bid')
            return $this->analyzeMakeOfferEvent($recInit, $subRecords);

        // Accept Offer
        if ($entrypoint == 'fulfill_bid')
            return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet, Enum\EventFlowDetails::OfferAccepted);

        // Cancel Offer
        if ($entrypoint == 'retract_bid')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    protected function analyzeMakeOfferEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $recValue = $recInit['parameter']['value'];

            $offer = new TokenActions\Offer();
            $offer->tokenAddress    = $recValue['fa2'];
            $offer->tokenId         = $recValue['objkt_id'];
            $offer->tokenAmount     = 1;
            $offer->creator         = $recValue['artist'];
            $offer->buyer           = $recInit['sender'];
            $offer->price           = $recInit['amount'];

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
}
