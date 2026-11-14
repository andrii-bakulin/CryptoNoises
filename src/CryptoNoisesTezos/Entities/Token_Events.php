<?php

namespace CryptoNoisesTezos\Entities;

use CryptoNoisesTezos\Config;
use CryptoNoisesTezos\Models;

trait Token_Events
{
    public function fixEvent_tokenDidMint(): void
    {
        if (Models\Events\EventsTokenMint::model()->fix($this->contractAdid, $this->tokenUnid) == false)
            return;

        if (Config::kTOKEN_EVENTS_FixMintAndUpdate_forProduction)
        {
            $this->fixEvent_withParams(
                Models\Queues\QueueTokensEvents::kScope_API,
                Models\Queues\QueueTokensEvents::kEventType_Mint);
        }

        if (Config::kTOKEN_EVENTS_FixMintAndUpdate_forDevelopment)
        {
            $this->fixEvent_withParams(
                Models\Queues\QueueTokensEvents::kScope_DEV,
                Models\Queues\QueueTokensEvents::kEventType_Mint);
        }
    }

    public function fixEvent_tokenDidUpdate(): void
    {
        if (Models\Events\EventsTokenUpdates::model()->fix($this->contractAdid, $this->tokenUnid) == false)
            return;

        if (Config::kTOKEN_EVENTS_FixMintAndUpdate_forProduction)
        {
            $this->fixEvent_withParams(
                Models\Queues\QueueTokensEvents::kScope_API,
                Models\Queues\QueueTokensEvents::kEventType_Update);
        }

        if (Config::kTOKEN_EVENTS_FixMintAndUpdate_forDevelopment)
        {
            $this->fixEvent_withParams(
                Models\Queues\QueueTokensEvents::kScope_DEV,
                Models\Queues\QueueTokensEvents::kEventType_Update);
        }
    }

    private function fixEvent_withParams(string $scope, string $eventType): void
    {
        Models\Queues\QueueTokensEvents::model()->push([
            'contract_adid' => $this->contractAdid,
            'token_unid'    => $this->tokenUnid,
            'scope'         => $scope,
            'event_type'    => $eventType,
        ]);
    }
}
