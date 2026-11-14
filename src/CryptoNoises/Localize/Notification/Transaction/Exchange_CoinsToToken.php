<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;

class Exchange_CoinsToToken extends AbstractTransaction
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
                '🔄🟡 You swapped <b>{###AMOUNT_TOTAL###}</b> ➡️ <b>{###TOKEN_AMOUNT_WITH_CURRENCY###}</b>',
                '- Exchange: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You swapped tokens'];

            $this->description = [
                '- Sent: {###AMOUNT_TOTAL###}',
                '- Received: {###TOKEN_AMOUNT_WITH_CURRENCY###}',
                self::EMPTY_LINE,
                '- Exchange: '.$this->presetMarketplace(),
            ];

            $this->color = self::COLOR_YELLOW;
        }
    }
}
