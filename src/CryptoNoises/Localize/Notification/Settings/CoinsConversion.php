<?php

namespace CryptoNoises\Localize\Notification\Settings;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class CoinsConversion extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    private int $currencyAddedCount = 0;

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        $this->title = [
            "Coin conversion",
        ];

        $this->description = [
            'All displayed amounts will be auto-converted to the chosen currencies.',
        ];
    }

    public function appendCurrencyName(string $currencyName): void
    {
        if ($this->currencyAddedCount == 0)
        {
            $this->description[] = self::EMPTY_LINE;
            $this->description[] = 'You selected:';
        }

        $this->description[] = "➡️ $currencyName";

        $this->currencyAddedCount++;
    }

    public function nothingSelected(): void
    {
        $this->footer[] = "⚠️ You haven't chosen any currencies yet";
    }
}
