<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Bots\Core\SlashCommand;
use CryptoNoises\Config;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class Help extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        $this->title = [
            "Bot commands"
        ];

        $this->appendInfoTitle('🔵 WALLETS');
        $this->appendInfoDescription([
            'Add your wallets to track your balances as well as any events in the blockchain associated with those wallets like:',
            '▫️ Incoming/outgoing cryptocurrency or token transfers;',
            '▫️ NFT purchases/sales;',
            '▫️ NFT royalties from secondary sales;',
            '▫️ NFTs or tokens you send/receive for free;',
            '▫️ Auction events (start, bids, outbids, etc);',
            '▫️ Swaps between tokens and coins;',
        ]);
        $this->appendSplitLine();
        $this->appendInfoCommand(SlashCommand::SUMMARY,         'Summary of all your wallets');
        $this->appendInfoCommand(SlashCommand::WALLETS_LIST,    'List of all your wallets + settings');
        $this->appendInfoCommand(SlashCommand::ADD_WALLET,      'Add wallet to track');
        $this->appendInfoCommand(SlashCommand::DELETE_WALLET,   'Stop tracking a wallet');
        $this->appendSplitter();

        $this->appendInfoTitle('🟠 FOLLOW USERS');
        $this->appendInfoDescription([
            'Follow users and get notified when a user:',
            '▫️ mints a token;',
            '▫️ lists a token they created;',
            '▫️ starts an auction for a token they created;',
        ]);
        $this->appendSplitLine();
        $this->appendInfoCommand(SlashCommand::FOLLOWING,       'List of users you follow');
        $this->appendInfoCommand(SlashCommand::FOLLOW,          'Follow user');
        $this->appendInfoCommand(SlashCommand::UNFOLLOW,        'Unfollow user');
        $this->appendSplitter();

        $this->appendInfoTitle('🔴 EXTRA FEATURES');
        $this->appendInfoDescription([
            'A few more useful commands',
        ]);
        $this->appendSplitLine();
        $this->appendInfoCommand(SlashCommand::DOMAINS,         'Your domains');
        $this->appendInfoCommand(SlashCommand::OFFERS,          'Your active offers on marketplaces');
        $this->appendInfoCommand(SlashCommand::REDIRECT_DM,     'Redirect all DM notifications to your own Discord channel');
        $this->appendInfoCommand(SlashCommand::ABOUT_TOKEN,     'Show information about any NFT');
        $this->appendInfoCommand(SlashCommand::COINS,           'Coin rates');
        $this->appendSplitter();

        $this->appendInfoTitle('⚪️ OTHERS');
        $this->appendSplitLine();
        $this->appendInfoCommand(SlashCommand::SETTINGS,        'Customize bot as you like');
        $this->appendSplitter();

        // Attribution required by the TzKT and CoinGecko API terms
        $this->appendInfoDescription([Phrase::I()->dataSourcesAttribution]);

        $this->appendLinkButtonsRow([
            Phrase::I()->actionJoinTwitter   => Config::cfg()->getLink('twitter'),
            Phrase::I()->actionJoinDiscord   => Config::cfg()->getLink('discord_invite'),
            Phrase::I()->actionPrivacyPolicy => Config::cfg()->getLink('privacy_policy'),
        ]);
    }

    private function appendInfoTitle(string $title): self
    {
        if (self::IsTelegram())
        {
            $this->description[] = "<b>$title</b>";
        }
        else if(self::IsDiscord())
        {
            $this->description[] = "**$title**";
        }
        return $this;
    }

    private function appendInfoDescription(array $description): self
    {
        $this->description = array_merge($this->description, $description);
        return $this;
    }

    private function appendInfoCommand(string $command, string $description): self
    {
        if (self::IsTelegram())
        {
            $this->description[] = '<code>'.$this->wrapCommand($command).'</code>'.' - '.$description;
        }
        else if(self::IsDiscord())
        {
            $this->description[] = $this->wrapCommand($command, true).' - '.$description;
        }
        return $this;
    }

    private function appendSplitLine(): self
    {
        $this->description[] = '-----------------------------------------------';
        return $this;
    }

    private function appendSplitter(): self
    {
        $this->description[] = self::EMPTY_LINE;
        $this->description[] = self::EMPTY_LINE;
        return $this;
    }
}
