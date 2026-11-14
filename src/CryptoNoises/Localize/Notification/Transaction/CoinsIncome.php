<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class CoinsIncome extends AbstractTransaction
{
    public static function ByQuantity(?User $user, ?int $quantity): self
    {
        return new self($user, (int)$quantity);
    }

    public function __construct(?User $user, int $quantity)
    {
        parent::__construct($user);

        if (self::IsTelegram())
        {
            if ($quantity > 1)
                $this->description[] = "🟢 {###QUANTITY###} incoming transactions totaling <b>{###AMOUNT_TOTAL###}</b>";
            else
                $this->description[] = "🟢 Incoming transaction of <b>{###AMOUNT_TOTAL###}</b>";

            $this->description[] = "{###AMOUNT_TOTAL_CONVERTED###}";
            $this->description[] = "- Sender: ".$this->presetSenderProfile();
        }
        else if(self::IsDiscord())
        {
            if ($quantity > 1)
                $this->title[] = "{###QUANTITY###} incoming transactions totaling {###AMOUNT_TOTAL###}";
            else
                $this->title[] = "Incoming transaction of {###AMOUNT_TOTAL###}";

            $this->description[] = "{###AMOUNT_TOTAL_CONVERTED###}";
            $this->description[] = "- Sender: ".$this->presetSenderProfile();

            $this->buttons[] = [
                NotificationButton::Link('Sender',              '{###SENDER_PROFILE_URL###}'),
            ];

            $this->color = self::COLOR_GREEN;
        }
    }
}
