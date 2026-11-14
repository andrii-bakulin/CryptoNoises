<?php

namespace CryptoNoises\Localize\Notification\Redirect;

use CryptoNoises\Bots\Core\SlashCommand;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class HowToActivateFromDM extends Notification
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
            "Redirect all DM notifications to your own Discord channel",
        ];

        $this->description = [
            'By using this command you may redirect all DM (Direct Message) notifications from the bot to one of the channels of your Discord server.',
            self::EMPTY_LINE,
            'To do this, complete the following steps:',
            '1️⃣ Invite CryptoNoises Bot to your server',
            '2️⃣ Open the channel where you want to receive notifications from the bot',
            '3️⃣ Execute command '.$this->wrapCommand(SlashCommand::REDIRECT_DM).' and click on `'.Phrase::I()->redirectActivate.'` button',
            self::EMPTY_LINE,
            "After that, all notifications will be published on that channel and you'll stop receiving notifications in DM.",
        ];

        $this->footer[] = "⚠️ You must be an Administrator of your server or have the 'Manage Server' permission.";
    }
}
