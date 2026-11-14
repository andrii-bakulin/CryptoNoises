<?php

namespace CryptoNoises\Localize\Notification\Wallets;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class Details extends Notification
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

        if (self::IsTelegram())
        {
            $this->description = [
                "{###WALLET_ICON###} <b>{###WALLET_NAME###}</b>",
                "<a href=\"{###WALLET_PROFILE_URL###}\">{###WALLET_ADDRESS###}</a>",
            ];

            $this->footer = [
                "Balance: <b>{###WALLET_BALANCE###}</b>",
                "{###WALLET_BALANCE_CONVERTED###}",
            ];
        }
        else if(self::IsDiscord())
        {
            $this->url = '{###WALLET_PROFILE_URL###}';

            $this->title = [
                "{###WALLET_ICON###} **{###WALLET_NAME###}**",
            ];

            $this->description = [
                "`{###WALLET_ADDRESS###}`",
            ];

            $this->footer = [
                "Balance: {###WALLET_BALANCE###} {###WALLET_BALANCE_CONVERTED###}",
            ];

            $this->thumbnailUrl = 'https://services.tzkt.io/v1/avatars/{###WALLET_ADDRESS###}';
        }
    }
}
