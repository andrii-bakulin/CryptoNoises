<?php

namespace CryptoNoises\Core;

use CryptoNoises\Bots\Connections\TelegramBotConnection;
use CryptoNoises\Config;

class Debugger
{
    protected static ?TelegramBotConnection $tgBotConnection = null;

    protected static function bot(): TelegramBotConnection
    {
        if (self::$tgBotConnection === null)
        {
            self::$tgBotConnection = new TelegramBotConnection(TELEGRAM_DEBUGGER_API);
            self::$tgBotConnection->initUserByTelegramIds(TELEGRAM_DEBUGGER_USER_ID, TELEGRAM_DEBUGGER_USER_ID);
        }

        return self::$tgBotConnection;
    }

    public static function Fatal(string $message): void
    {
        self::Notify('fatal', $message);
    }

    public static function Error(string $message): void
    {
        self::Notify('error', $message);
    }

    public static function Warning(string $message): void
    {
        self::Notify('warning', $message);
    }

    public static function Notice(string $message): void
    {
        self::Notify('notice', $message);
    }

    public static function Notify(string $level, string $message): void
    {
        switch ($level)
        {
            case 'fatal':   $level = '🚨 FATAL';    break;
            case 'error':   $level = '🛑 ERROR';    break;
            case 'warning': $level = '⚠️ Warning';  break;
            case 'notice':  $level = '💬 Notice';   break;
            default:        $level = '';            break;
        }

        $environment = Config::cfg()->isProduction() ? 'PRO' : (Config::cfg()->isDevelopmentLocal() ? 'DEV-LOCAL' : 'DEV');

        self::bot()->sendMessage("[$environment] $level: $message");
    }
}
