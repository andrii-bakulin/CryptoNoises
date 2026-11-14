<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class AboutToken_HowToUse extends Notification
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

        $this->title = ["🌅 Please specify the token address and token ID."];
        $this->description = [
            '/about_token TOKEN_ADDRESS TOKEN_ID',
            self::EMPTY_LINE,
            'For example:',
            '/about_token KT1QgwGzMC3WZmkjJX2NxRQJjevCzM1cf6Ux 0',
        ];
    }
}
