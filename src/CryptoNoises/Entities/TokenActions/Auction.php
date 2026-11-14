<?php

namespace CryptoNoises\Entities\TokenActions;

class Auction
{
    public ?string $tokenAddress;
    public ?string $tokenId;

    public ?string $creator;

    public ?float $highestBid;
}
