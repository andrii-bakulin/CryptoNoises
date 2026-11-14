<?php

namespace CryptoNoises\Bots\Threads;

use CryptoNoises\Bots\Core\SlashCommand;

class TelegramCommandsThread
{
    public static function GenerateCommandsList(): array
    {
        return [
            '/'.SlashCommand::SUMMARY                   => '📊 Summary of wallets',
            '/'.SlashCommand::WALLETS_LIST              => 'ℹ️ Wallets list',
            '/'.SlashCommand::ADD_WALLET                => '➕ Add wallet',

            '/'.SlashCommand::FOLLOWING                 => '👥 Users you follow',
            '/'.SlashCommand::FOLLOW                    => '👥🔔 Follow',
            '/'.SlashCommand::UNFOLLOW                  => '👥🔕 Unfollow',

            '/'.SlashCommand::DOMAINS                   => '🌐 Your domains',
            '/'.SlashCommand::OFFERS                    => '🤝 Your offers on marketplaces',
            '/'.SlashCommand::COINS                     => '💱 Coin rates',

            '/'.SlashCommand::ABOUT_TOKEN               => '🌅 View details about a token',

            '/'.SlashCommand::SETTINGS                  => '⚙️ Settings',
            '/'.SlashCommand::HELP                      => '🤔 Help',

        ];
    }
}
