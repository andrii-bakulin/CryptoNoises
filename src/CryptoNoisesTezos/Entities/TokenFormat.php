<?php

namespace CryptoNoisesTezos\Entities;

class TokenFormat
{
    public string $uri;
    public ?string $fileName = null;
    public ?string $mimetype = null;
    public ?int $fileSize = null;
    public ?string $duration = null;
    public ?string $dimensionValue = null;
    public ?string $dimensionUnit = null;
    public ?string $dataRateValue = null;
    public ?string $dataRateUnit = null;
}
