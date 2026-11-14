<?php

namespace CryptoNoisesTezos\Core;

use CryptoNoisesTezos\Models;

class Log
{
    public static function Fatal($scope, $action, $message, $data = null): void
    {
        self::Save('fatal', $scope, $action, $message, $data);
    }

    public static function Error($scope, $action, $message, $data = null): void
    {
        self::Save('error', $scope, $action, $message, $data);
    }

    public static function Warning($scope, $action, $message, $data = null): void
    {
        self::Save('warning', $scope, $action, $message, $data);
    }

    public static function Notice($scope, $action, $message, $data = null): void
    {
        self::Save('notice', $scope, $action, $message, $data);
    }

    private static function Save($level, $scope, $action, $message, $data = null): void
    {
        Models\Log\Errors::model()->save(['level' => $level, 'scope' => $scope, 'action' => $action, 'message' => $message, 'data' => $data,]);
    }
}
