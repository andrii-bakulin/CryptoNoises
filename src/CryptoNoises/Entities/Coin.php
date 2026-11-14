<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Helpers\Helper;
use CryptoNoises\Models;

class Coin
{
    protected float $amount;
    protected string $currency;

    public function __construct($amount, $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getFriendlyValue(): string
    {
        $currencyItem = Models\CurrencyOut::model()->getItemById($this->currency);

        $value = number_format($this->amount, $currencyItem['decimals']);

        return Helper::TrimRightZerosInNumber($value).' '.$this->currency;
    }
}
