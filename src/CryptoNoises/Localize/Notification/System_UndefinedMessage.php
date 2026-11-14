<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Bots\Core\SlashCommand;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class System_UndefinedMessage extends Notification
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
            $this->title = ["Sorry, I don't understand this message."];
        }
        else if(self::IsDiscord())
        {
            $this->plainText = "Sorry, I don't understand this message.".PHP_EOL.
                "You can start with ".$this->wrapCommand(SlashCommand::HELP)." command";
        }
    }
}
