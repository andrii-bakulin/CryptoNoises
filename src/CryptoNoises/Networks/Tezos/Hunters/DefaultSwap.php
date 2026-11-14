<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosNetwork;

// Swap sequences in one operation, for example: XTZ > uUSD, or uUSD > kDAO > YOU > XTZ
// (coins-to-token, token-to-token and token-to-coins steps)

class DefaultSwap extends AbstractHunter
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

            if ($this->analyzeSwap_coinsToToken($recInit, $subRecords, $transfer))
                return true;

            if ($this->analyzeSwap_tokenToCoins($recInit, $subRecords, $transfer))
                return true;
        }
        else if (count($recTransfers) == 2)
        {
            $transfer0 = $this->unpackSingleTransferEvent($recTransfers[0]);
            $transfer1 = $this->unpackSingleTransferEvent($recTransfers[1]);

            if ($transfer0 === null || $transfer1 === null)
                return false;

            if ($this->analyzeSwap_tokenToToken($recInit, $subRecords, $transfer0, $transfer1))
                return true;

            if ($this->analyzeSwap_tokenToToken($recInit, $subRecords, $transfer1, $transfer0))
                return true;
        }

        return false;
    }

    //------------------------------------------------------------------------------------------------------------------

    private function analyzeSwap_coinsToToken(array $recInit, array $subRecords, TokenActions\Transfer $transfer): bool
    {
        if ($recInit['amount'] == 0)
            return false;

        $amount = $recInit['amount'];

        $sender = $recInit['sender'];   // tz..
        $target = $recInit['target'];   // KT..

        if (!TezosNetwork::IsWalletAddressValid($sender))       return false;
        if (!TezosNetwork::IsContractAddressValid($target))     return false;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $feeRecord = null;

        // Allow find 1 record as "fee payment"
        foreach ($subRecords as $subRecord)
        {
            if ($subRecord['amount'] > 0)
            {
                if ($feeRecord === null)
                {
                    $feeRecord = $subRecord;
                    continue;
                }

                // If I have other transfer XTZ-coins in sub-records -> this is not coinsToToken
                // It's "maybe" new purchase in some store...
                return false;
            }
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($transfer->sender == $target && $transfer->target == $sender)
        {   // $target sending token back

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::ExchangeCoinsToToken;
            $event->sender_address          = $sender;                      // tz..-user address (swap-initiator!!!)
            $event->target_address          = $target;                      // exchanger-contract address (not token)

            $event->price                   = $amount;                      // amount in XTZ
            $event->currency                = TezosNetwork::kCURRENCY_ID;   // currency=XTZ

            $event->revenue                 = $transfer->quantity;          // amount in tokens
            $event->currency_address        = $transfer->tokenAddress.':'.$transfer->tokenId;

            $event->marketplace_address     = $target;
            $this->parser->pushEvent($event);
        }
        else if ($transfer->sender == $target && TezosNetwork::IsWalletAddressValid($transfer->target))
        {   // $target sending token to external wallet: tz1.. >--XTZ--> KT1.. >--TOKEN--> tz2..

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit, 1);

            $event->event_type              = Enum\EventFlowType::CoinTransfer;
            $event->sender_address          = $sender;
            $event->target_address          = $target;
            $event->price                   = $amount;
            $event->currency                = TezosNetwork::kCURRENCY_ID;

            $this->parser->pushEvent($event);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit, 2);

            $event->event_type              = Enum\EventFlowType::TokenTransfer;
            $event->sender_address          = $transfer->sender;
            $event->target_address          = $transfer->target;

            $event->quantity                = $transfer->quantity;

            $event->token_address           = $transfer->tokenAddress;
            $event->token_id                = $transfer->tokenId;

            $this->parser->pushEvent($event);
        }
        else
        {
            return false;
        }

        if ($feeRecord !== null)
        {
            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit, 3);

            $event->event_type              = Enum\EventFlowType::CoinTransfer;
            $event->sender_address          = $feeRecord['sender'];
            $event->target_address          = $feeRecord['target'];
            $event->price                   = $feeRecord['amount'];
            $event->currency                = TezosNetwork::kCURRENCY_ID;

            $this->parser->pushEvent($event);
        }

        return true;
    }

    private function analyzeSwap_tokenToCoins(array $recInit, array $subRecords, TokenActions\Transfer $transfer): bool
    {
        $sender = $recInit['sender'];   // tz..
        $target = $recInit['target'];   // KT..

        if (!TezosNetwork::IsWalletAddressValid($sender))       return false;
        if (!TezosNetwork::IsContractAddressValid($target))     return false;

        // $sender sending token
        if ($transfer->sender != $sender || $transfer->target != $target)
            return false;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $amount = null;

        foreach ($subRecords as $record)
        {
            if ($record['amount'] == 0)
                continue;

            if ($record['sender'] != $target || $record['target'] != $sender)
                return false;

            $amount += $record['amount'];
        }

        if ($amount == 0)
            return false;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $event = new EventFlow();
        $event->initInTezosNetworkByRecord($recInit);

        $event->event_type              = Enum\EventFlowType::ExchangeTokenToCoins;
        $event->sender_address          = $sender;                      // tz..-user address (swap-initiator!!!)
        $event->target_address          = $target;                      // exchanger-contract address (not token)

        $event->price                   = $transfer->quantity;          // amount in tokens
        $event->currency_address        = $transfer->tokenAddress.':'.$transfer->tokenId;

        $event->revenue                 = $amount;                      // amount in XTZ
        $event->currency                = TezosNetwork::kCURRENCY_ID;   // currency=XTZ

        $event->marketplace_address     = $target;
        $this->parser->pushEvent($event);

        return true;
    }

    private function analyzeSwap_tokenToToken(array $recInit, array $subRecords, TokenActions\Transfer $transfer1, TokenActions\Transfer $transfer2): bool
    {
        $sender = $recInit['sender'];   // tz..
        $target = $recInit['target'];   // KT..

        if (!TezosNetwork::IsWalletAddressValid($sender))       return false;
        if (!TezosNetwork::IsContractAddressValid($target))     return false;

        // $transfer1: sender -> target
        // $transfer2: target -> sender
        if (!($transfer1->sender == $sender && $transfer1->target == $target &&
              $transfer2->sender == $target && $transfer2->target == $sender))
            return false;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $event = new EventFlow();
        $event->initInTezosNetworkByRecord($recInit);

        $event->event_type              = Enum\EventFlowType::ExchangeTokenToToken;
        $event->sender_address          = $sender;                      // tz..-user address (swap-initiator!!!)
        $event->target_address          = $target;                      // exchanger-contract address (not token)

        $event->price                   = $transfer1->quantity;          // amount in tokens
        $event->currency_address        = $transfer1->tokenAddress.':'.$transfer1->tokenId;

        $event->revenue                 = $transfer2->quantity;          // amount in tokens
        $event->currency_address_2nd    = $transfer2->tokenAddress.':'.$transfer2->tokenId;

        $event->marketplace_address     = $target;
        $this->parser->pushEvent($event);

        return true;
    }
}
