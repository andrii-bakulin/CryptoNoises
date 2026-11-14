<?php

namespace CryptoNoises\ControllersApi;

class TelegramAssistController extends AbstractServiceBotController
{
    protected function apiKey(): string         { return TELEGRAM_ASSISTANT_API; }
    protected function ownerUserId(): int       { return (int) TELEGRAM_ASSISTANT_USER_ID; }
    protected function botName(): string        { return 'Assist'; }
}
