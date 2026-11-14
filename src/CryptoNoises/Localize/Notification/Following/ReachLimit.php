<?php

namespace CryptoNoises\Localize\Notification\Following;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class ReachLimit extends Notification
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
            "⚠️ You have reached the limit for the number of users you can follow",
        ];

        $this->description = ["You have reached the limit of {$this->user->limits->followingCount} users you can follow."];
    }
}
