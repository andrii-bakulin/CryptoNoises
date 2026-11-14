<?php

namespace CryptoCore\Http;

class SyncResult
{
    public ?string $url                     = null;
    public /* ?|string|array */ $postData   = null;
    public ?int $code                       = null;
    public /* ?|string|array */ $data       = null;
    public ?int $iteration                  = null;

    public function fixResult($code, $data): void
    {
        $this->code = $code;
        $this->data = $data;
    }

    public function isSuccess(): bool
    {
        return $this->code == 200;
    }

    public function notSuccess(): bool
    {
        return !$this->isSuccess();
    }
}
