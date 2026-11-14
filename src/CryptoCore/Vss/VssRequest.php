<?php

namespace CryptoCore\Vss;

class VssRequest
{
    public function get($key, $default = null)
    {
        if (isset($_POST[$key]))
            return $_POST[$key];

        if (isset($_GET[$key]))
            return $_GET[$key];

        return $default;
    }

    public function getUriVar($key)
    {
        return Vss::f3()->get('PARAMS.' . $key);
    }
}
