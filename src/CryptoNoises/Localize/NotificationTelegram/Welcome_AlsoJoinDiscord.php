<?php

namespace CryptoNoises\Localize\NotificationTelegram;

use CryptoNoises\Config;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class Welcome_AlsoJoinDiscord extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = null; // no need

    //------------------------------------------------------------------------------------------------------------------

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        $this->title = [
            "<b>CryptoNoises Bot is also available for Discord</b>",
        ];

        $this->description = [
            "To activate the bot for Discord, <a href='".htmlspecialchars((string) Config::cfg()->getLink('discord_invite'), ENT_QUOTES)."'>join our official Discord</a> and say 'hi' to CryptoNoises Bot. You'll find it in the users' list.",
        ];
    }
}
