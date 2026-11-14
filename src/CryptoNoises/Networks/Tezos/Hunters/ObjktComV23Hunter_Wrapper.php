<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Enum;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Networks\Tezos\TezosNetwork;

class ObjktComV23Hunter_Wrapper extends ObjktComV23Hunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if (!$this->setupHunterAttrsByContract($recInit['target']))
            return false;

        $entrypoint = $recInit['entrypoint'];

        if ($entrypoint == 'wrap')
            return $this->analyzeWrapEvent($recInit, $subRecords);

        if ($entrypoint == 'unwrap')
            return $this->analyzeUnwrapEvent($recInit, $subRecords);

        return false;
    }

    private function analyzeWrapEvent(array $recInit, array $subRecords): bool
    {
        $transferRecord = $recInit;

        $event = new EventFlow();
        $event->initInTezosNetworkByRecord($recInit);

        $event->event_type              = Enum\EventFlowType::ExchangeCoinsToToken;
        $event->sender_address          = $transferRecord['sender'];    // tz..-user address (swap-initiator!!!)
        $event->target_address          = $transferRecord['target'];    // token-contract address

        $event->price                   = $transferRecord['amount'];    // amount in XTZ
        $event->currency                = TezosNetwork::kCURRENCY_ID;

        $event->revenue                 = $transferRecord['amount'];    // amount in oXTZ (wXTZ)
        $event->currency_address        = self::kWrapperKTID;

        $event->marketplace_address     = $this->kMarketplaceKTID;
        $this->parser->pushEvent($event);
        return true;
    }

    private function analyzeUnwrapEvent(array $recInit, array $subRecords): bool
    {
        if (count($subRecords) != 1)
        {
            Log::Warning(get_class($this), __FUNCTION__, "Sub-records count is not ONE", $recInit);
            return false;
        }

        $transferRecord = reset($subRecords);

        $event = new EventFlow();
        $event->initInTezosNetworkByRecord($recInit);

        $event->event_type              = Enum\EventFlowType::ExchangeTokenToCoins;
        $event->sender_address          = $recInit['target'];           // exchanger-contract address (not token)
        $event->target_address          = $recInit['sender'];           // tz..-user address

        $event->price                   = $transferRecord['amount'];    // amount in oXTZ (wXTZ)
        $event->currency_address        = self::kWrapperKTID;

        $event->revenue                 = $transferRecord['amount'];    // amount in XTZ
        $event->currency                = TezosNetwork::kCURRENCY_ID;

        $event->marketplace_address     = $this->kMarketplaceKTID;
        $this->parser->pushEvent($event);
        return true;
    }
}
