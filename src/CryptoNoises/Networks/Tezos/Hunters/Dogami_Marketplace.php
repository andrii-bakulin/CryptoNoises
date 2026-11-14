<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Enum;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class Dogami_Marketplace extends Dogami
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        if ($recInit['entrypoint'] == 'addToMarketplace')
            return $this->addToMarketplace($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'removeFromMarketplace')
            return $this->removeFromMarketplace($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'collect')
            return $this->collect($recInit, $subRecords);

        return false;
    }

    private function addToMarketplace(array $recInit, array $subRecords): bool
    {
        try
        {
            $value = $recInit['parameter']['value'];

            if ($value['token_symbol'] != 'XTZ')
                throw new Exception("Unknown token_symbol [{$value['token_symbol']}]");

            if (!isset($value['swap_type']['regular']))
                throw new Exception("Unknown swap_type (not 'regular')");

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = self::kTokensKTID;
            $listing->tokenId           = $value['token_id'];

            $listing->price             = $value['token_price'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = 1;

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenList;
            $event->sender_address          = $recInit['sender'];
            $event->target_address          = $recInit['target'];

            $event->price                   = $listing->price;
            $event->currency                = $listing->currency;
            $event->quantity                = $listing->quantity;

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = $listing->tokenAddress;
            $event->token_id                = $listing->tokenId;

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }

    private function removeFromMarketplace(array $recInit, array $subRecords): bool
    {
        return $this->fixEventAsSkipped($recInit, $subRecords);
    }

    private function collect(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet);
    }
}
