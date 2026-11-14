<?php

namespace CryptoNoises\ControllersApi;

class TelegramDebugController extends AbstractServiceBotController
{
    protected function apiKey(): string         { return TELEGRAM_DEBUGGER_API; }
    protected function ownerUserId(): int       { return (int) TELEGRAM_DEBUGGER_USER_ID; }
    protected function botName(): string        { return 'Debugger'; }
}
