<?php

namespace CryptoCore\Vss;

abstract class VssModelRecord
{
    abstract protected function pack(): ?array;

    abstract protected function unpack(?array $item): void;
}
