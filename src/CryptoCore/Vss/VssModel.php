<?php

namespace CryptoCore\Vss;

class VssModel
{
    public static function model($className = __CLASS__)
    {
        static $instances = array();

        if (isset($instances[$className]) == false)
            $instances[$className] = new $className;

        return $instances[$className];
    }
}
