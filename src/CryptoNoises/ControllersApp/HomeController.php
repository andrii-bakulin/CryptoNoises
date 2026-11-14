<?php

namespace CryptoNoises\ControllersApp;

use CryptoNoises\Config;
use CryptoNoises\Core\HttpRedirect;

class HomeController
{
    public function index(): void
    {
        HttpRedirect::ToOr404(Config::cfg()->getLink('homepage'));
    }
}
