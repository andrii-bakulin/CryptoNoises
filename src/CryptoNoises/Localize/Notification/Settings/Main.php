<?php

namespace CryptoNoises\Localize\Notification\Settings;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class Main extends Notification
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

        $this->title = [
            "Settings",
        ];

        if (self::IsTelegram())
        {
            $this->description = [
                "<b>".Phrase::I()->actionCoinsConversion."</b>",
                "All displayed amounts will be auto-converted to the chosen currencies.",
                self::EMPTY_LINE,
                "<b>".Phrase::I()->actionFilterNotifications."</b>",
                "Choose what kind of notifications you want to receive or ignore.",
                self::EMPTY_LINE,
                "<b>".Phrase::I()->filterThresholds."</b>",
                "Set minimum amounts for notifications you want to receive.",
            ];
        }
        else if(self::IsDiscord())
        {
            $this->appendFieldValue(Phrase::I()->actionCoinsConversion,
                'All displayed amounts will be auto-converted to the chosen currencies.');

            $this->appendFieldValue(Phrase::I()->actionFilterNotifications,
                'Choose what kind of notifications you want to receive or ignore.');

            $this->appendFieldValue(Phrase::I()->filterThresholds,
                'Set minimum amounts for notifications you want to receive.');
        }
    }
}
