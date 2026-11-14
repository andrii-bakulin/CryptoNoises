<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class TokenTransfer_Receive extends AbstractTransaction
{
    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        if (self::IsTelegram())
        {
            $this->description = [
                '🟢 Incoming transaction of <b>{###TOKEN_AMOUNT_WITH_CURRENCY###}</b>',
                '- Sender: '.$this->presetSenderProfile(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['Incoming transaction of {###TOKEN_AMOUNT_WITH_CURRENCY###}'];

            $this->description = [
                '- Sender: '.$this->presetSenderProfile(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Sender',              '{###SENDER_PROFILE_URL###}'),
            ];

            $this->color = self::COLOR_GREEN;
        }
    }
}
