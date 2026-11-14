<?php

namespace CryptoNoises\Entities\TokenActions;

class CollectionOffer
{
    public ?string $buyer;
    public ?string $creator;

    public ?string $collectionAddress;
    public ?string $collectionId;
    public ?int $tokenAmount;

    public ?float $price;
}
