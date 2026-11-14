<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosNetwork;

// Transfer XTZ [sender -> target] and some token in the same direction [sender -> target]

class DefaultLiquidity extends AbstractHunter
{
    public static function GetAllMarketContracts(): array
    {
        return [];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        return null;
    }

    //------------------------------------------------------------------------------------------------------------------

    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        $recTransfers = [];

        while ($recTransfer = self::ExtractRecordByEntrypoint($subRecords, 'transfer'))
        {
            $recTransfers[] = $recTransfer;
        }

        if (count($recTransfers) == 1)
        {
            $transfer = $this->unpackSingleTransferEvent($recTransfers[0]);

            if ($transfer === null)
                return false;

            if ($this->analyzeInvestLiquidity($recInit, $subRecords, $transfer))
                return true;
        }

        return false;
    }

    //------------------------------------------------------------------------------------------------------------------

    private function analyzeInvestLiquidity(array $recInit, array $subRecords, TokenActions\Transfer $transfer): bool
    {
        if ($recInit['amount'] == 0)
            return false;

        $amount = $recInit['amount'];

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $sender = $recInit['sender'];   // tz..
        $target = $recInit['target'];   // KT..

        if (!TezosNetwork::IsWalletAddressValid($sender))       return false;
        if (!TezosNetwork::IsContractAddressValid($target))     return false;

        // $sender sending token to $target (same as coins!)
        if ($transfer->sender != $sender || $transfer->target != $target)
            return false;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $event = new EventFlow();
        $event->initInTezosNetworkByRecord($recInit);

        $event->event_type              = Enum\EventFlowType::LiquidityInvest;
        $event->sender_address          = $sender;
        $event->target_address          = $target;

        $event->price                   = $amount;
        $event->currency                = TezosNetwork::kCURRENCY_ID;

        $event->revenue                 = $transfer->quantity;
        $event->currency_address        = $transfer->tokenAddress.':'.$transfer->tokenId;

        $event->marketplace_address     = $target;
        $this->parser->pushEvent($event);

        return true;
    }
}

