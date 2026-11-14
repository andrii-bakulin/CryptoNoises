<?php

namespace CryptoNoises\Entities\TokenActions;

class Swap
{
    public int $swapId;
    public ?string $seller;

    public ?string $tokenAddress;
    public ?string $tokenId;

    public function __construct(int $swapId)
    {
        $this->swapId = $swapId;
    }
}
