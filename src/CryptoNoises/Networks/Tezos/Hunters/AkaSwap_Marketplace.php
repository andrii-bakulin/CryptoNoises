<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class AkaSwap_Marketplace extends AkaSwap
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        if ($recInit['entrypoint'] == 'swap')
            return $this->swap($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'collect')
            return $this->analyzePurchaseEvent($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'cancel_swap')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    private function swap(array $recInit, array $subRecords): bool
    {
        try
        {
            $valueSwap = $recInit['parameter']['value'];

            if (!self::IsSupportedTokensContract($valueSwap['token_fa2']))
                throw new Exception("Unknown token-address #{$valueSwap['token_fa2']}");

            if ($valueSwap['xtz_per_token'] == 0)
                throw new Exception("XTZ price is ZERO");

            //----------------------------------------------------------------------------------------------------------

            $transfer = $this->unpackTransfersEvent($subRecords);

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = $transfer->tokenAddress;
            $listing->tokenId           = $transfer->tokenId;

            $listing->price             = $valueSwap['xtz_per_token'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = $transfer->quantity;

            $listing->creator           = null;
            $listing->royalty_d4        = null;

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenList;
            $event->sender_address          = $transfer->sender;
            $event->target_address          = $transfer->target;
            $event->royalty_address         = $listing->creator;

            $event->price                   = $listing->price;
            $event->currency                = $listing->currency;
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
