<?php

namespace CryptoNoises\Localize\NotificationTelegram;

use CryptoNoises\Config;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class System_DiscordCommand extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = null; // no need

    //------------------------------------------------------------------------------------------------------------------

    public static function I(): self
    {
        return new self(null);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        $this->title = ["This command is available only in the Discord bot"];

        $this->appendLinkButtonsRow([
            Phrase::I()->actionJoinTwitter => Config::cfg()->getLink('twitter'),
            Phrase::I()->actionJoinDiscord => Config::cfg()->getLink('discord_invite'),
        ]);
    }
}
