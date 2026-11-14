<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Localize\Notification;

class Custom extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    public static function I(?string $title = null): self
    {
        $notification = new self(null);

        if ($title)
            $notification->setTitle($title);

        return $notification;
    }
}
