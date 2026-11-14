<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

abstract class AbstractTransaction extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = false;

    //------------------------------------------------------------------------------------------------------------------

    public function __construct(?User $user)
    {
        parent::__construct($user);

        if (self::IsTelegram())
        {
            $this->footer = [
                "Balance: <b>{###WALLET_BALANCE###}</b>",
                "{###WALLET_BALANCE_CONVERTED###}",
                '@ <a href="{###WALLET_PROFILE_URL###}">{###WALLET_NAME###}</a>',
            ];
        }
        else if(self::IsDiscord())
        {
            $this->footer = [
                "Balance: {###WALLET_BALANCE###}",
                "{###WALLET_BALANCE_CONVERTED###}",
                '@ {###WALLET_NAME###}',
            ];
        }
    }
}
