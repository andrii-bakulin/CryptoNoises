<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class TokenTransfer_Send extends AbstractTransaction
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
                '🔴 Outgoing transaction of <b>{###TOKEN_AMOUNT_WITH_CURRENCY###}</b>',
                '- Receiver: '.$this->presetTargetProfile(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['Outgoing transaction of {###TOKEN_AMOUNT_WITH_CURRENCY###}'];

            $this->description = [
                '- Receiver: '.$this->presetTargetProfile(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Receiver',            '{###TARGET_PROFILE_URL###}'),
            ];

            $this->color = self::COLOR_RED;
        }
    }
}
