<?php

namespace CryptoCore\Vss;

class VssDbExp
{
    public string $sql = '';

    public function __construct($sql)
    {
        $this->sql = " ( $sql ) ";
    }

    public static function query($sql): VssDbExp
    {
        return new VssDbExp($sql);
    }

    public static function now($deltaSeconds = 0): VssDbExp
    {
        $sql = "NOW() + INTERVAL $deltaSeconds SECOND";
        return new VssDbExp($sql);
    }
}
