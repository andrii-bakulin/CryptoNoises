<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class TokenTransfer_Send_Undefined extends AbstractTransaction
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
                '🔴 You sent <b>{###QUANTITY###} tokens</b> #{###TOKEN_ID###}',
                '- Contract: '.$this->presetTokenContractInfo(),
                '- Receiver: '.$this->presetTargetProfile(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You sent {###QUANTITY###} tokens #{###TOKEN_ID###}'];

            $this->description = [
                '- Receiver: '.$this->presetTargetProfile(),
                '- Contract: '.$this->presetTokenContractInfo(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Receiver',            '{###TARGET_PROFILE_URL###}'),
                NotificationButton::Link('Contract',            '{###TOKEN_CONTRACT_URL###}'),
            ];

            $this->color = self::COLOR_RED;
        }
    }
}
