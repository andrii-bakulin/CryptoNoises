<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;

class DomainExpired extends Notification
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

        if (self::IsTelegram())
        {
            $this->description = [
                '🔴 Domain "{###DOMAIN###}" expired',
                '- Expired at: <b>{###EXPIRE_DATETIME###}</b>',
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['Domain "{###DOMAIN###}" expired'];

            $this->appendFieldValue('Expired at', '{###EXPIRE_DATETIME###}');

            $this->color = self::COLOR_RED;
        }

        $this->buttons[] = [
            NotificationButton::Link('Renew domain', '{###DOMAIN_RENEW_URL###}'),
        ];
    }
}
