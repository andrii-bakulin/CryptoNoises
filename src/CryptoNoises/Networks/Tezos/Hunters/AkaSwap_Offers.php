<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosHelper;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class AkaSwap_Offers extends AkaSwap
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kOfferKTID)
            return false;

        if ($recInit['entrypoint'] == 'make_offer')
            return $this->makeOffer($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'cancel_offer')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'fulfill_offer')
            return $this->analyzePurchaseEvent($recInit, $subRecords, Enum\EventFlowDetails::OfferAccepted);

        return false;
    }

    private function makeOffer(array $recInit, array $subRecords): bool
    {
        try
        {
            $tokenAddress = $recInit['parameter']['value']['token_fa2'];
            $tokenId      = $recInit['parameter']['value']['token_id'];

            if (!self::IsSupportedTokensContract($tokenAddress))
                throw new Exception("Unknown token-address #$tokenAddress");

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenOffer;
            $event->sender_address          = $recInit['sender'];
            $event->offer_owners_addresses  = TezosHelper::GetTokenHolders($tokenAddress, $tokenId);
            $event->royalty_address         = null;

            $event->price                   = $recInit['amount'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;
            $event->quantity                = 1;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = $tokenAddress;
            $event->token_id                = $tokenId;

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}", $recInit);
            return false;
        }
    }
}
