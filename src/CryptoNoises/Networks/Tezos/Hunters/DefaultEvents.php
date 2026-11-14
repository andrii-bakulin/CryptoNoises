<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\Hunters;
use CryptoNoises\Networks\Tezos\Hunters\Analyzers;
use CryptoNoises\Networks\Tezos\TezosNetwork;

class DefaultEvents extends AbstractHunter
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
        // A single transaction can contain both a coin and a token transfer, so both are always analyzed

        Analyzers\StorePurchase::Analyze($recInit, $subRecords);

        $foundCoinsTransfer = $this->parseTransaction_coinsTransfer($recInit, $subRecords);

        $foundTokensTransfer = $this->parseTransaction_tokensTransfer($recInit, $subRecords);

        if (!$foundTokensTransfer)
            Analyzers\UntrackedTransfer::Analyze($recInit, $subRecords);

        if ($foundCoinsTransfer || $foundTokensTransfer)
            return true;

        return false;
    }

    //------------------------------------------------------------------------------------------------------------------
    // Coins Transfer

    private static ?array $SPEC_CONTRACTS = null;

    private function getAllHuntersKTIDs(): array
    {
        if (self::$SPEC_CONTRACTS === null)
        {
            self::$SPEC_CONTRACTS = [];

            foreach (TezosNetwork::$HunterClasses as $hunterClass)
            {
                $KTIDs = call_user_func("$hunterClass::GetAllMarketContracts");

                if (is_array($KTIDs))
                    self::$SPEC_CONTRACTS = array_merge(self::$SPEC_CONTRACTS, $KTIDs);
            }

            self::$SPEC_CONTRACTS = array_unique(self::$SPEC_CONTRACTS);
        }
        return self::$SPEC_CONTRACTS;
    }

    private function parseTransaction_coinsTransfer(array $recInit, array $subRecords): bool
    {
        $foundCount = (int) $this->analyzeEvent($recInit);

        foreach ($subRecords as $record)
        {
            $foundCount += (int) $this->analyzeEvent($record);
        }

        if ($foundCount == 0)
            return false;

        if (in_array($recInit['target'], $this->getAllHuntersKTIDs()))
        {
            // A plain coin transfer to a marketplace contract (no entrypoint) is not reported
            if (!empty($recInit['entrypoint']))
            {
                Log::Warning(get_class($this), __FUNCTION__,
                    "Coin transfer to #{$recInit['target']} level={$recInit['level']} hash={$recInit['hash']} @ {$recInit['entrypoint']}",
                    $recInit);
            }
        }

        return true;
    }

    private function analyzeEvent(array $record): bool
    {
        if ($record['amount'] == 0)
            return false;

        $event = new EventFlow();
        $event->initInTezosNetworkByRecord($record);

        $event->event_type      = Enum\EventFlowType::CoinTransfer;
        $event->sender_address  = $record['sender'];
        $event->target_address  = $record['target'];
        $event->price           = $record['amount'];
        $event->currency        = TezosNetwork::kCURRENCY_ID;

        $this->parser->pushEvent($event);
        return true;
    }

    //------------------------------------------------------------------------------------------------------------------
    // Tokens Transfer

    private function parseTransaction_tokensTransfer(array $recInit, array $subRecords): bool
    {
        $transfersCount = 0;

        $transfersCount += $this->findAndPushTransferEvents($recInit, true);

        foreach ($subRecords as $record)
        {
            $transfersCount += $this->findAndPushTransferEvents($record);
        }

        return $transfersCount > 0;
    }

    private function findAndPushTransferEvents(array $record, bool $autolinkMarketplaceAddress=false): int
    {
        $transferEvents = $this->findTransferEvents($record);

        if (empty($transferEvents))
            return 0;

        $usageCounts = [];

        foreach ($transferEvents as $event)
        {
            // Why groupScope is needed:
            //
            // (1) A user transfers one kind of token to different targets in one transaction.
            //     The 8 transfers are grouped into ONE for the sender, but stay 8 for the targets.
            //     Counters (for one token): sender = 8 (once), target = 1 (each of 8 times).
            //
            // (2) A user transfers N different tokens at once, each time with the same sender-target pair.
            //     Counters (for each token): sender = 1, target = 1.

            $groupScope = $event->token_address.':'.$event->token_id;

            $usageCounts["$groupScope:sender"][$event->sender_address] = ($usageCounts["$groupScope:sender"][$event->sender_address] ?? 0) + 1;
            $usageCounts["$groupScope:target"][$event->target_address] = ($usageCounts["$groupScope:target"][$event->target_address] ?? 0) + 1;
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($autolinkMarketplaceAddress)
            $marketplaceAddress = Hunters\AbstractHunter::Convert_TokenAddressToMarketplace($record['target']);
        else
            $marketplaceAddress = null;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // Important to regenerate `entity_subid` indexes
        $subIdIndex = 0;

        foreach ($transferEvents as $event)
        {
            $event->marketplace_address = $marketplaceAddress;

            $groupScope = $event->token_address.':'.$event->token_id;

            if ($usageCounts["$groupScope:sender"][$event->sender_address] > 1 ||
                $usageCounts["$groupScope:target"][$event->target_address] > 1)
            {
                $transferEventSent = clone $event;
                $transferEventSent->event_type          = Enum\EventFlowType::TokenTransfer_Send;
                $transferEventSent->entity_subid        = ++$subIdIndex;

                $transferEventReceived = clone $event;
                $transferEventReceived->event_type      = Enum\EventFlowType::TokenTransfer_Receive;
                $transferEventReceived->entity_subid    = ++$subIdIndex;

                $this->parser->pushEvent($transferEventSent);
                $this->parser->pushEvent($transferEventReceived);
            }
            else
            {
                $event->entity_subid = ++$subIdIndex;
                $this->parser->pushEvent($event);
            }
        }

        return count($transferEvents);
    }
}
