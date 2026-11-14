<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use Exception;

class ObjktComV23Hunter_Main extends ObjktComV23Hunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if (!$this->setupHunterAttrsByContract($recInit['target']))
            return false;

        $entrypoint = $recInit['entrypoint'];

        // List
        if ($entrypoint == 'ask')
            return $this->analyzeListEvent($recInit, $subRecords);

        // Unlist
        if ($entrypoint == 'retract_ask')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        // Direct Purchase
        if ($entrypoint == 'fulfill_ask')
        {
            if ($this->analyzeTokenPurchaseEvent_withTokens($recInit, $subRecords, $this->kMarketplaceFeeWallet))
                return true;

            return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, $this->kMarketplaceFeeWallet);
        }

        return false;
    }

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            if (count($subRecords) != 0)
                Log::Warning(get_class($this), __FUNCTION__, "List event has unexpected sub-records", $recInit);
            // continue anyway

            //----------------------------------------------------------------------------------------------------------

            $recValue = $recInit['parameter']['value'];

            $listing = new TokenActions\Listing();
            $listing->tokenAddress          = $recValue['token']['address'];
            $listing->tokenId               = $recValue['token']['token_id'];

            $listing->price                 = $recValue['amount'];
            $listing->currency              = $this->detectCurrencyIdOrToken($recValue['currency']);
            $listing->quantity              = $recValue['editions'];

            $listing->creator               = $this->getAddressWithHighestShareAmount($recValue['shares']);
            $listing->royalty_d4            = $this->getTotalShareAmount($recValue['shares']); // Incoming value for 25% is '2500' => no need to convert

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenList;
            $event->sender_address          = $recInit['sender'];
            $event->target_address          = $recInit['target'];
            $event->royalty_address         = $listing->creator;

            $event->price                   = $listing->price;
            $event->currency                = $listing->isCoinsCurrency() ? $listing->currency : null;
            $event->currency_address        = $listing->isTokenCurrency() ? $listing->currency : null;
            $event->quantity                = $listing->quantity;

            $event->royalty_d4              = $listing->royalty_d4;

            $event->marketplace_address     = $recInit['target'];
            $event->token_address           = $listing->tokenAddress;
            $event->token_id                = $listing->tokenId;

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
