<?php

namespace CryptoNoises\Bots\Results;

class AddWalletsResult
{
    public array $walletsList = [];
    public array $addressSuccessList = [];
    public array $addressFailedList = [];
    public bool $didReachLimit = false;
}
