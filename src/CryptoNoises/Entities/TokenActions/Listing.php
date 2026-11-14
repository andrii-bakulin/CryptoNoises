<?php

namespace CryptoNoises\Entities\TokenActions;

class Listing
{
    public ?string $tokenAddress;
    public ?string $tokenId;

    public ?float $price;
    public ?string $currency;
    public ?float $quantity;

    public ?string $creator;
    public ?int $royalty_d4;        // 25% => decimal 4 => 2500

    public ?int $duration = null;
    public ?bool $burnOnEnd = null;

    public function isCoinsCurrency(): bool
    {
        return $this->currency && strlen($this->currency) < 32;
    }

    public function isTokenCurrency(): bool
    {
        return $this->currency && strlen($this->currency) >= 32;
    }
}
