<?php

namespace CryptoNoises\Localize\NotificationTelegram;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class System_OnlySupportDM extends Notification
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

        $this->title = ["CryptoNoises Bot can be used only in a private chat"];
    }
}
