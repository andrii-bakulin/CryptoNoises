<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class TokenTransfer_Receive_Undefined extends AbstractTransaction
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
                '🟢 You received <b>{###QUANTITY###} tokens</b> #{###TOKEN_ID###}',
                '- Contract: '.$this->presetTokenContractInfo(),
                '- Sender: '.$this->presetSenderProfile(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You received {###QUANTITY###} tokens #{###TOKEN_ID###}'];

            $this->description = [
                '- Sender: '.$this->presetSenderProfile(),
                '- Contract: '.$this->presetTokenContractInfo(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Sender',              '{###SENDER_PROFILE_URL###}'),
                NotificationButton::Link('Contract',            '{###TOKEN_CONTRACT_URL###}'),
            ];

            $this->color = self::COLOR_GREEN;
        }
    }
}
