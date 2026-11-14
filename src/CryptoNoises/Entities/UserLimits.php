<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Config;

/**
 * Per-user limits. The same for every user, set in config/environment.php (api.limits).
 */
class UserLimits
{
    public int $walletsCount;
    public int $followingCount;

    public function __construct()
    {
        $this->walletsCount     = Config::cfg()->limitWallets;
        $this->followingCount   = Config::cfg()->limitFollowing;
    }
}
