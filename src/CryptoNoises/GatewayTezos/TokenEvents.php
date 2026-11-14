<?php

namespace CryptoNoises\GatewayTezos;

use CryptoNoisesTezos\Models;

class TokenEvents
{
    public static function GetEvent_NewTokenDidMint(): ?\CryptoNoises\Entities\Token
    {
        $scope = \CryptoNoises\Config::cfg()->isProduction()
            ? Models\Queues\QueueTokensEvents::kScope_API
            : Models\Queues\QueueTokensEvents::kScope_DEV;

        return self::GetEventByParams($scope, Models\Queues\QueueTokensEvents::kEventType_Mint);
    }

    public static function GetEvent_MetadataDidUpdate(): ?\CryptoNoises\Entities\Token
    {
        $scope = \CryptoNoises\Config::cfg()->isProduction()
            ? Models\Queues\QueueTokensEvents::kScope_API
            : Models\Queues\QueueTokensEvents::kScope_DEV;

        return self::GetEventByParams($scope, Models\Queues\QueueTokensEvents::kEventType_Update);
    }

    //------------------------------------------------------------------------------------------------------------------

    private static function GetEventByParams(string $scope, string $eventType): ?\CryptoNoises\Entities\Token
    {
        $queueItem = Models\Queues\QueueTokensEvents::model()->pop($scope, $eventType);

        if ($queueItem === null)
            return null;

        return Tokens::GetTokenDetailsByInnerParams($queueItem['contract_adid'], $queueItem['token_unid'], true);
    }
}
