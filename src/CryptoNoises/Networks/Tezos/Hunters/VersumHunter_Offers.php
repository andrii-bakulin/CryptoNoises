<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosHelper;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class VersumHunter_Offers extends VersumHunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        if ($recInit['entrypoint'] =='make_offer')
            return $this->analyzeMakeOfferEvent($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'accept_offer')
            return $this->analyzePurchaseEvent($recInit, $subRecords, Enum\EventFlowDetails::OfferAccepted);

        if ($recInit['entrypoint'] == 'cancel_offer')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    /**
     * @param array $recInit
     * @param array $subRecords
     * @return bool
     */
    protected function analyzeMakeOfferEvent(array $recInit, array $subRecords): bool
    {
        $logData = $recInit;

        try
        {
            $recValue = $recInit['parameter']['value'];

            if (!in_array($recValue['token']['address'], self::GetSupportedCollectionKTIDs()))
                throw new Exception("Unsupported collection contract ".$recValue['token']['address']);

            if ($recValue['currency'] !== null)
                throw new Exception("Non-XTZ currency is not supported");

            if ($recInit['amount'] == 0)
                throw new Exception("Offer price is zero");

            //----------------------------------------------------------------------------------------------------------

            $offer = new TokenActions\Offer();
            $offer->tokenAddress  = $recValue['token']['address'];
            $offer->tokenId       = $recValue['token']['nat'];
            $offer->tokenAmount   = $recValue['token_amount'];
            $offer->creator       = null;
            $offer->buyer         = $recInit['sender'];
            $offer->price         = $recInit['amount'];

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenOffer;
            $event->sender_address          = $offer->buyer;
            $event->offer_owners_addresses  = TezosHelper::GetTokenHolders($offer->tokenAddress, $offer->tokenId);
            $event->royalty_address         = null;

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
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}", $logData);
            return false;
        }
    }
}
