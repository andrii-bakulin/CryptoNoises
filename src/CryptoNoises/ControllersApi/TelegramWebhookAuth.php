<?php

namespace CryptoNoises\ControllersApi;

/**
 * Authorizes incoming Telegram webhook calls by the "X-Telegram-Bot-Api-Secret-Token" header,
 * which Telegram sends when the webhook was registered with secret_token (see commands: /telegram/webhook/set).
 *
 * Unauthorized calls get a silent 403: nothing is logged or forwarded to the debugger bot.
 */
final class TelegramWebhookAuth
{
    public static function Authorize(): bool
    {
        $expected = defined('TELEGRAM_WEBHOOK_SECRET') ? (string) TELEGRAM_WEBHOOK_SECRET : '';
        $received = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? null;

        if ($expected !== '' && is_string($received) && hash_equals($expected, $received))
            return true;

        http_response_code(403);
        return false;
    }
}
