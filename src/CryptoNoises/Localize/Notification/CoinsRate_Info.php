<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Entities\Coin;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;
use CryptoCore\Time;

class CoinsRate_Info extends Notification
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
            "Coin rates"
        ];

        $this->footer = [
            Time::NowByMask('H:i:s, M d, Y'),         # 12:33:59, Dec 03, 2021
            Phrase::I()->poweredByCoinGecko,          // required by the CoinGecko API terms
        ];
    }

    public function appendRate(string $currencyInId, Coin $coin): void
    {
        $key = "1 $currencyInId";

        if ($coin->getAmount() > 0)
            $value = $coin->getFriendlyValue();
        else
            $value = "?.??? ".$coin->getCurrency();

        if (self::IsTelegram())
        {
            $this->description[] = "<b>$key</b> = $value";
        }
        else if (self::IsDiscord())
        {
            $this->appendFieldValue($key, $value);
            $this->setFieldsAsInline(true);
        }
    }

    public function nothingSelected(): void
    {
        $this->title = [
            "You didn't select any coins to track",
        ];

        if ($this->warning == '')
            $this->warning = "Click ".Phrase::I()->actionSettings." to choose";
    }

    public function appendActionStep1(): void
    {
        $this->warning = "Choose the cryptocurrency you want to track";
    }

    public function appendActionStep2($currencyInName): void
    {
        $this->warning = "Choose the currency you want to convert $currencyInName to";
    }
}
