<?php

namespace CryptoNoises\Entities\TokenActions;

class Transfer
{
    public string $sender;
    public string $target;

    public string $tokenAddress;
    public string $tokenId;

    public ?float $quantity;
}
