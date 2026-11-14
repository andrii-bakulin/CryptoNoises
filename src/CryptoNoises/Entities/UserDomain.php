<?php

namespace CryptoNoises\Entities;

use CryptoCore\Time;

class UserDomain
{
    const kACTIVE                               = 1;
    const kEXPIRE_SOON                          = 2;
    const kEXPIRED                              = 3;

    public string $domain;
    public string $address;
    public int $state;
    public string $expireDateTime;
    public int $expireDuration;

    public function __construct(string $domain, string $address, string $expireDateTime)
    {
        $this->domain           = $domain;
        $this->address          = $address;
        $this->expireDateTime   = $expireDateTime;
        $this->expireDuration   = strtotime($expireDateTime) - Time::Time();

        if ($this->expireDuration <= 0)
        {
            $this->state = self::kEXPIRED;
        }
        else if ($this->expireDuration < 30 * 86400)
        {
            $this->state = self::kEXPIRE_SOON;
        }
        else
        {
            $this->state = self::kACTIVE;
        }
    }
}
