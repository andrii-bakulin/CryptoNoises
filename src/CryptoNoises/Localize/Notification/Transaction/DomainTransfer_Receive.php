<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class DomainTransfer_Receive extends AbstractTransaction
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
                '🟢 You have received "'.$this->presetNftName().'" domain',
                '- Sender: '.$this->presetSenderProfile(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You have received "'.$this->presetNftName().'" domain'];
            $this->url   = '{###TOKEN_STORE_URL###}';

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
