<?php

namespace CryptoNoisesTezos\Core\Http;

final class HttpResponse
{
    public int $code;
    public ?string $body;
    public ?string $error;

    public function __construct(int $code, ?string $body, ?string $error = null)
    {
        $this->code  = $code;
        $this->body  = $body;
        $this->error = $error;
    }

    public static function Failed(string $error): self
    {
        return new self(0, null, $error);
    }

    public function isOk(): bool
    {
        return $this->code == 200;
    }
}
