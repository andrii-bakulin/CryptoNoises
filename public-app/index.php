<?php

use CryptoCore\Vss\Vss;

require_once __DIR__ . "/../vendor/autoload.php";

VssCfg::cfg()->initialize();
Vss::initialize();

$f3 = Vss::f3();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$f3->route('GET             /collection/@scope/@address',       'CryptoNoises\ControllersApp\RedirectController->collection');
$f3->route('GET             /collection/@scope',                'CryptoNoises\ControllersApp\RedirectController->collection');
$f3->route('GET             /contract/@scope/@address',         'CryptoNoises\ControllersApp\RedirectController->contract');
$f3->route('GET             /token/@scope/@address/@token',     'CryptoNoises\ControllersApp\RedirectController->token');
$f3->route('GET             /token/@scope/@token',              'CryptoNoises\ControllersApp\RedirectController->token');
$f3->route('GET             /user/@scope/@address',             'CryptoNoises\ControllersApp\RedirectController->user');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$f3->route('GET|POST|HEAD   /*',                                'CryptoNoises\ControllersApp\HomeController->index');
$f3->route('GET|POST|HEAD   /',                                 'CryptoNoises\ControllersApp\HomeController->index');

Vss::f3()->run();
