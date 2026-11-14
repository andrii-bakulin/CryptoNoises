<?php

namespace CryptoNoises\Core;

use Exception;

class CryException extends Exception
{
    public $data = null;

    public function __construct($message, $data=null)
    {
        parent::__construct($message);
        $this->data = $data;
    }
}
