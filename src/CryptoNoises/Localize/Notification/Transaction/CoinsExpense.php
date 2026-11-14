<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class CoinsExpense extends AbstractTransaction
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
                $this->description[] = "🔴 {###QUANTITY###} outgoing transactions totaling <b>{###AMOUNT_TOTAL###}</b>";
            else
                $this->description[] = "🔴 Outgoing transaction of <b>{###AMOUNT_TOTAL###}</b>";

            $this->description[] = "{###AMOUNT_TOTAL_CONVERTED###}";
            $this->description[] = "- Receiver: ".$this->presetTargetProfile();
        }
        else if(self::IsDiscord())
        {
            if ($quantity > 1)
                $this->title[] = "{###QUANTITY###} outgoing transactions totaling {###AMOUNT_TOTAL###}";
            else
                $this->title[] = "Outgoing transaction of {###AMOUNT_TOTAL###}";

            $this->description[] = "{###AMOUNT_TOTAL_CONVERTED###}";
            $this->description[] = "- Receiver: ".$this->presetTargetProfile();

            $this->buttons[] = [
                NotificationButton::Link('Receiver',            '{###TARGET_PROFILE_URL###}'),
            ];

            $this->color = self::COLOR_RED;
        }
    }
}
