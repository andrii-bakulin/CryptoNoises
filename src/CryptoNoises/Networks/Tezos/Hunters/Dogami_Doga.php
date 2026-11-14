<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Enum;
use CryptoNoises\Entities\EventFlow;
use Exception;

class Dogami_Doga extends Dogami
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kDogaKTID)
            return false;

        if ($recInit['entrypoint'] == 'transfer')
            return $this->transfer($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'transferBatch')
            return $this->transferBatch($recInit, $subRecords);

        return false;
    }

    private function transfer(array $recInit, array $subRecords): bool
    {
        $recValue = $recInit['parameter']['value'];

        try
        {
            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenTransfer;
            $event->sender_address          = $recValue['from'];
            $event->target_address          = $recValue['to'];
            $event->quantity                = $recValue['value'];

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = $recInit['target'];
            $event->token_id                = 0;

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }

    private function transferBatch(array $recInit, array $subRecords): bool
    {
        $recValue = $recInit['parameter']['value'];

        try
        {
            if (!is_array($recValue))
                throw new Exception('Value-data should be an array, but it is not');

            foreach ($recValue as $idx => $item)
            {
                $event = new EventFlow();
                $event->initInTezosNetworkByRecord($recInit, $idx+1);

                $event->event_type              = Enum\EventFlowType::TokenTransfer;
                $event->sender_address          = $item['from'];
                $event->target_address          = $item['to'];
                $event->quantity                = $item['value'];

                $event->marketplace_address     = self::kMarketplaceKTID;
                $event->token_address           = $recInit['target'];
                $event->token_id                = 0;

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
