<?php

namespace CryptoNoises\Localize\Notification\Settings;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class FilterByTypes extends Notification
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
            "Filter by notification type",
        ];

        $this->description = [
            'Choose what kind of notifications you want to receive or ignore.',
            self::EMPTY_LINE,
            "Also, you're able to control these filters for each of your wallets. Just show the wallets list and open settings for the required one.",
        ];

        $this->footer = [
            "⚠️ If you block notifications here, then you will not receive notifications regardless of the settings of each wallet."
        ];
    }
}
