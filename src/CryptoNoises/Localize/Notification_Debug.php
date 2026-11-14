<?php

namespace CryptoNoises\Localize;

use CryptoNoises\Config;
use CryptoNoises\Entities\Marketplace;
use CryptoNoises\Entities\Token;

trait Notification_Debug
{
    protected array $debugData = [];

    public function getDebugStamp(array $appendData = []): array
    {
        return array_merge($this->debugData, $appendData);
    }

    public function bindDebugClassName(): self
    {
        $className = get_class($this);
        $this->debugData['className'] = $className;

        if (Config::cfg()->isDevelopment())
        {
            $this->footer[] = Notification::EMPTY_LINE;
            $this->footer[] = str_replace('CryptoNoises\\Localize\\Notification\\', '', $className);
        }
        return $this;
    }

    public function bindDebugArray(string $dataKey, ?array $data, ?string $attachKeyToNotification = null): self
    {
        $this->debugData[$dataKey] = $data;

        if (Config::cfg()->isDevelopment() && $attachKeyToNotification)
            $this->footer[] = "$dataKey #".$data[$attachKeyToNotification];

        return $this;
    }

    public function bindDebugToken(string $dataKey, ?Token $token): self
    {
        if ($token)
        {
            $this->debugData[$dataKey] = (array) $token;
        }
        return $this;
    }

    public function bindDebugMarketplace(?Marketplace $marketplace): self
    {
        if ($marketplace)
            $this->debugData['marketplace'] = $marketplace->address;

        return $this;
    }
}
