<?php

namespace CryptoNoises\Entities\TokenActions;

class Offer
{
    public ?string $buyer;
    public ?string $creator;

    public ?string $tokenAddress;
    public ?string $tokenId;
    public ?int $tokenAmount;

    public ?float $price;
    public ?string $currency;

    public function isCoinsCurrency(): bool
    {
        return $this->currency && strlen($this->currency) < 32;
    }

    public function isTokenCurrency(): bool
    {
        return $this->currency && strlen($this->currency) >= 32;
    }
}
