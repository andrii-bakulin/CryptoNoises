<?php

namespace CryptoNoises\Localize\Notification\Settings;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class FilterThresholds extends Notification
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
            "Thresholds",
        ];
    }
}
