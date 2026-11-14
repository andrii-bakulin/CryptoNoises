<?php

namespace CryptoNoises\ControllersApi;

use CryptoNoises\Bots\Connections\TelegramBotConnection;
use CryptoNoises\Config;
use CryptoNoises\Core\Log;
use CryptoNoises\Helpers\Helper;
use Exception;

/**
 * Webhook of a private service bot (assistant / debugger): answers only its owner in a private chat.
 */
abstract class AbstractServiceBotController
{
    abstract protected function apiKey(): string;

    abstract protected function ownerUserId(): int;

    abstract protected function botName(): string;

    public function webhook(): void
    {
        Config::cfg()->initializeByPlatform(Config::kPLATFORM_Telegram);

        if (!TelegramWebhookAuth::Authorize())
            return;

        try
        {
            $tgBotConnection = new TelegramBotConnection($this->apiKey());

            if ($tgBotConnection->webhook->init() == false)
                throw new Exception("Failed to parse the webhook request");

            if (!isset($tgBotConnection->user))
                throw new Exception("User not initialized");

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            if ($tgBotConnection->user->platformUserId != $this->ownerUserId() || !$tgBotConnection->webhook->isPrivateChat())
            {
                $tgBotConnection->sendMessage("Access denied");
                throw new Exception("Access denied");
            }

            if ($this->webhookMessage($tgBotConnection) == false)
                $tgBotConnection->sendMessage("Unknown command");
        }
        catch (Exception $e)
        {
            $errorData = [];
            $errorData['channel_id']   = TelegramBotConnection::UnpackBotId($this->apiKey());
            $errorData['data_request'] = file_get_contents("php://input");

            Log::Fatal(get_class($this), __FUNCTION__, $e->getMessage(), $errorData);
        }
    }

    private function webhookMessage(TelegramBotConnection $tgBotConnection): bool
    {
        $message = trim($tgBotConnection->webhook->getMessageText());

        list ($commandId) = Helper::SplitBySpaces($message, 2);

        switch (strtolower($commandId))
        {
            case '/start':
                $tgBotConnection->sendMessage("Welcome to ".$this->botName());
                return true;

            default:
                return false;
        }
    }
}
