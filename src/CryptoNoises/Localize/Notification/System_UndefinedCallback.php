<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class System_UndefinedCallback extends Notification
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

        $this->title = ["Sorry, I don't understand this request."];
    }
}
