<?php

namespace CryptoNoises\Entities;

class Offer
{
    public Token $token;
    public Marketplace $marketplace;

    public string $buyerAddress;

    public ?string $priceAmount;
    public ?string $priceCurrency;
    public ?string $priceFriendly;
}
