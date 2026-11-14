<?php

use CryptoCore\Vss\Vss;

require_once __DIR__ . "/../vendor/autoload.php";

VssCfg::cfg()->initialize();
Vss::initialize();

$f3 = Vss::f3();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$f3->route('GET|POST        /telegram/webhook',                 'CryptoNoises\ControllersApi\TelegramBotController->webhook');
$f3->route('GET|POST        /telegram/assist',                  'CryptoNoises\ControllersApi\TelegramAssistController->webhook');
$f3->route('GET|POST        /telegram/debug',                   'CryptoNoises\ControllersApi\TelegramDebugController->webhook');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$f3->route('GET|POST|HEAD   /*',                                'CryptoNoises\ControllersApi\HomeController->index');
$f3->route('GET|POST|HEAD   /',                                 'CryptoNoises\ControllersApi\HomeController->index');

Vss::f3()->run();
