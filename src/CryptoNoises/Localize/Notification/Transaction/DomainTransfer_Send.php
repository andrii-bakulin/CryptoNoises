<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class DomainTransfer_Send extends AbstractTransaction
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
                '🔴 You transferred the "'.$this->presetNftName().'" domain to a new owner',
                '- Receiver: '.$this->presetTargetProfile(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You transferred the "'.$this->presetNftName().'" domain to a new owner'];
            $this->url   = '{###TOKEN_STORE_URL###}';

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
