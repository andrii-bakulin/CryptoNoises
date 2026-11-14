<?php

namespace CryptoNoises\Localize\Notification\Wallets;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class SummaryInfo_Item extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = null; // not used for this notification

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
            // One-item for "message"
            $this->description = [
                "{###WALLET_ICON###} <b><a href=\"{###WALLET_PROFILE_URL###}\">{###WALLET_NAME###}</a></b>",
                "<code>{###WALLET_ADDRESS###}</code>",
                "Balance: <b>{###WALLET_BALANCE###}</b>",
                "{###WALLET_BALANCE_CONVERTED###}",
            ];

            // One-item for "footer"
            $this->footer = [
                "<b>{###TOTAL_BALANCE###}</b>",
                "{###TOTAL_BALANCE_CONVERTED###}",
            ];
        }
        else if(self::IsDiscord())
        {
            // One-item for "embed.field". 1st line is "name", other lines are "value"
            $this->description = [
                "{###WALLET_ICON###} **{###WALLET_NAME###}**",
                "`{###WALLET_ADDRESS###}`",
                "Balance: **{###WALLET_BALANCE###}**",
                "{###WALLET_BALANCE_CONVERTED###}",
            ];

            // One-item for "footer"
            $this->footer = [
                "{###TOTAL_BALANCE###} {###TOTAL_BALANCE_CONVERTED###}",
            ];
        }
    }
}
