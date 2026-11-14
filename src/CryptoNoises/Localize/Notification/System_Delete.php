<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Localize\Notification;

class System_Delete extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    public static function I(): self
    {
        return new self(null);
    }
}
