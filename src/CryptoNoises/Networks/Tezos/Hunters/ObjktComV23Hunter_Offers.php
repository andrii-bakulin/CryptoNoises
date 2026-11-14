<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosHelper;
use Exception;

class ObjktComV23Hunter_Offers extends ObjktComV23Hunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if (!$this->setupHunterAttrsByContract($recInit['target']))
            return false;

        $entrypoint = $recInit['entrypoint'];

        // Make Offer
        if ($entrypoint == 'offer')
            return $this->analyzeMakeOfferEvent($recInit, $subRecords);

        // Accept Offer
        if ($entrypoint == 'fulfill_offer')
        {
            if ($this->analyzeTokenPurchaseEvent_withTokens($recInit, $subRecords, $this->kMarketplaceFeeWallet, Enum\EventFlowDetails::OfferAccepted))
                return true;

            return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, $this->kMarketplaceFeeWallet, Enum\EventFlowDetails::OfferAccepted);
        }

        // Cancel Offer
        if ($entrypoint == 'retract_offer')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    protected function analyzeMakeOfferEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $recValue = $recInit['parameter']['value'];

            // The shares list may be empty: the creator then stays unknown

            if ($recValue['token']['token_id'] === null)
            {
                // Many offers I got with undefined token_id (is null)
                //
                // This may happen for Tezzardz collection.
                // I think when token_id IS NULL it's like offer for any item in collection.
                //
                return true; // Handled (ignored): collection-wide offer without token_id
            }

            $offer = new TokenActions\Offer();
            $offer->tokenAddress    = (string) $recValue['token']['address'];
            $offer->tokenId         = (string) $recValue['token']['token_id'];
            $offer->tokenAmount     = 1;
            $offer->creator         = $this->getAddressWithHighestShareAmount($recValue['shares']);
            $offer->buyer           = $recInit['sender'];
            $offer->price           = $recValue['amount'];
            $offer->currency        = $this->detectCurrencyIdOrToken($recValue['currency']);

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenOffer;
            $event->sender_address          = $offer->buyer;
            $event->offer_owners_addresses  = TezosHelper::GetTokenHolders($offer->tokenAddress, $offer->tokenId);
            $event->royalty_address         = $offer->creator;

            $event->price                   = $offer->price;
            $event->currency                = $offer->isCoinsCurrency() ? $offer->currency : null;
            $event->currency_address        = $offer->isTokenCurrency() ? $offer->currency : null;
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
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}", $recInit);
            return false;
        }
    }
}
