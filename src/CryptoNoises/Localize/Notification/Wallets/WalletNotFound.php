<?php

namespace CryptoNoises\Localize\Notification\Wallets;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class WalletNotFound extends Notification
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
            "🤔 Wallet not found",
        ];

        $this->description = [
            "Maybe you deleted it before?",
        ];
    }
}
