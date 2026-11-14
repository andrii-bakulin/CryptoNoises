<?php

namespace CryptoCore\Env;

/**
 * isProduction() / isDevelopment() / isDevelopmentLocal() for a config class that stores Environment::name()
 * in its $environment property.
 */
trait EnvironmentPredicates
{
    public function isProduction(): bool
    {
        return $this->environment == Environment::kPRODUCTION;
    }

    public function isDevelopment(): bool
    {
        return $this->environment == Environment::kDEVELOPMENT || $this->environment == Environment::kDEVELOPMENT_LOCAL;
    }

    public function isDevelopmentLocal(): bool
    {
        return $this->environment == Environment::kDEVELOPMENT_LOCAL;
    }
}
