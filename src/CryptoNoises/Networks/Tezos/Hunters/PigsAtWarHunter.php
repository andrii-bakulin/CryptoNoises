<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class PigsAtWarHunter extends AbstractHunter
{
    public const kTokensKTID                        = 'KT19Emc76cfVr64pBjJauQRjm7p5tbEtVV8e';
    public const kTokensKTID_2nd                    = 'KT1TgYckAaLKrkWzYbNYNLaaS6eQpV44GGer';

    public const kMarketplaceKTID                   = 'KT1GNnH8N1DUCrL4gfvJJuZMnmjFwiCLBHZZ';

    public const kMarketplaceFeeWallet              = 'tz1U8t8vYbNPNi1xeGQZXJCqZb9STzH13H9U';

    //------------------------------------------------------------------------------------------------------------------

    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        self::ExtractRecordByEntrypoint($subRecords, 'internal_transfer_hook');

        if ($recInit['target'] == self::kMarketplaceKTID)
        {
            if ($recInit['entrypoint'] == 'create_auction')
                return $this->listing($recInit, $subRecords);

            if ($recInit['entrypoint'] == 'cancel_auction')
                return $this->fixEventAsSkipped($recInit, $subRecords);

            if ($recInit['entrypoint'] == 'accept_price')
                return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet);
        }

        if ($recInit['target'] == self::kTokensKTID ||
            $recInit['target'] == self::kTokensKTID_2nd)
        {
            // Handled here instead of DefaultEvents because this contract has double records for a transfer event:
            // 1. entrypoint = 'transfer'
            // 2. entrypoint = 'internal_transfer_hook'
            //
            // In this case default logic will show transfer event twice!

            if ($recInit['entrypoint'] == 'transfer')
                return $this->transfer($recInit, $subRecords);
        }

        return false;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        if ($tokensContract == self::kTokensKTID ||
            $tokensContract == self::kTokensKTID_2nd)
            return self::kMarketplaceKTID;

        return null;
    }

    //------------------------------------------------------------------------------------------------------------------

    private function listing(array $recInit, array $subRecords): bool
    {
        try
        {
            $recValue = $recInit['parameter']['value'];

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenList;
            $event->sender_address          = $recInit['sender'];
            $event->target_address          = $recInit['target'];

            $event->price                   = $recValue['price'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;
            $event->quantity                = 1;

            $event->marketplace_address     = self::kMarketplaceKTID;
            $event->token_address           = self::kTokensKTID;   // create_auction has no token contract: the first collection is assumed
            $event->token_id                = $recValue['token_id'];

            $this->parser->pushEvent($event);

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }

    private function transfer(array $recInit, array $subRecords): bool
    {
        try
        {
            $transferEvents = $this->findTransferEvents($recInit);

            if (empty($transferEvents))
                throw new Exception("Cannot unpack transfer-events");

            foreach ($transferEvents as $transferEvent)
            {
                $transferEvent->marketplace_address = self::kMarketplaceKTID;

                $this->parser->pushEvent($transferEvent);
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
