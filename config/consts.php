<?php

const kSEC_IN_MINUTE                        = 60;
const kSEC_IN_HOUR                          = 3600;

//----------------------------------------------------------------------------------------------------------------------

if (!is_file(__DIR__.'/consts-secrets.php'))
    die('Missing config/consts-secrets.php - copy config/consts-secrets.example.php and fill it in');

require_once(__DIR__.'/consts-secrets.php');
