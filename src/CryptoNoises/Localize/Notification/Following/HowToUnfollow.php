<?php

namespace CryptoNoises\Localize\Notification\Following;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class HowToUnfollow extends Notification
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
            $this->description[] = "ℹ️ To unfollow a user, send a Tezos domain or wallet address.";
            $this->description[] = self::EMPTY_LINE;
            $this->description[] = "If you want to unfollow everyone at once, just write <b>UNFOLLOW_ALL</b>";
        }
        else if(self::IsDiscord())
        {
            $this->title = ["ℹ️ Unfollow users"];

            $this->description[] = "Click the `".Phrase::I()->actionDelete."` button below and enter the Tezos domain or wallet address you want to unfollow.";
            $this->description[] = self::EMPTY_LINE;
            $this->description[] = "If you want to unfollow everyone at once, just write `UNFOLLOW_ALL`";
        }

        $this->footer[] = "You can send one or more domains/wallets separated by a space or a new line";
    }
}
