<?php

namespace CryptoNoises\Localize\Notification\Following;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class HowToFollow extends Notification
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
            $this->description[] = "ℹ️ To follow a user, send a Tezos domain or wallet address.";
        }
        else if(self::IsDiscord())
        {
            $this->title = ["ℹ️ Follow users"];

            $this->description[] = "Click the `".Phrase::I()->actionAdd."` button below and enter the Tezos domain or wallet address you want to follow.";
        }

        $this->footer[] = "You can send one or more domains/wallets separated by a space or a new line";
    }
}
