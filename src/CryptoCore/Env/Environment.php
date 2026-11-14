<?php

namespace CryptoCore\Env;

use VssCfg;

/**
 * Deployment-specific settings loaded from config/environment.php
 * (see config/environment.example.php).
 */
final class Environment
{
    const kPRODUCTION           = 'production';
    const kDEVELOPMENT          = 'development';
    const kDEVELOPMENT_LOCAL    = 'development-local';

    private const kALLOWED      = [self::kPRODUCTION, self::kDEVELOPMENT, self::kDEVELOPMENT_LOCAL];

    private static ?array $values = null;

    /**
     * @param string $key Dot-separated path, e.g. "tezos.cmd_ffmpeg"
     */
    public static function get(string $key, $default = null)
    {
        $value = self::load();

        foreach (explode('.', $key) as $part)
        {
            if (!is_array($value) || !array_key_exists($part, $value))
                return $default;

            $value = $value[$part];
        }

        return $value;
    }

    public static function name(): string
    {
        $name = self::get('environment');

        if (!in_array($name, self::kALLOWED, true))
            die('config/environment.php: "environment" must be one of: '.join(', ', self::kALLOWED));

        return $name;
    }

    private static function load(): array
    {
        if (self::$values === null)
        {
            $file = VssCfg::cfg()->pathTopCore.'/config/environment.php';

            if (!is_file($file))
                die('Missing config/environment.php - copy config/environment.example.php and adjust it');

            $values = require $file;

            if (!is_array($values))
                die('config/environment.php must return an array');

            self::$values = $values;
        }

        return self::$values;
    }
}
