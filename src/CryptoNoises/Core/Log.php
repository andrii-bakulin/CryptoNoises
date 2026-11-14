<?php

namespace CryptoNoises\Core;

use CryptoNoises\Config;
use CryptoNoises\Models;

class Log
{
    public static function Fatal($scope, $action, $message, $data=null)
    {
        self::Save('fatal', $scope, $action, $message, $data);
        self::Notify('fatal', $scope, $action, $message, $data);
    }

    public static function Error($scope, $action, $message, $data=null)
    {
        self::Save('error', $scope, $action, $message, $data);
        self::Notify('error', $scope, $action, $message, $data);
    }

    public static function Warning($scope, $action, $message, $data=null)
    {
        self::Save('warning', $scope, $action, $message, $data);
        self::Notify('warning', $scope, $action, $message, $data);
    }

    public static function Notice($scope, $action, $message, $data=null)
    {
        self::Save('notice', $scope, $action, $message, $data);
    }

    // A notice that is also sent to the operator chat (for example "bot started")
    public static function NoticeAndAlert($scope, $action, $message, $data=null)
    {
        self::Save('notice', $scope, $action, $message, $data);
        self::Notify('notice', $scope, $action, $message, $data);
    }

    //------------------------------------------------------------------------------------------------------------------

    private static function Save($level, $scope, $action, $message, $data=null)
    {
        Models\Log\Errors::model()->save([
            'level'     => $level,
            'scope'     => $scope,
            'action'    => $action,
            'message'   => $message,
            'data'      => $data,
        ]);
    }

    private static function Notify($level, $scope, $action, $message, $data=null)
    {
        Debugger::Notify($level, "$scope.$action: $message");
    }

    //------------------------------------------------------------------------------------------------------------------

    private static function Event(string $eventId, ?string $platform, ?int $userId=null, ?string $message=null): void
    {
        Models\Log\Events::model()->fix($eventId, $platform, $userId, $message);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function Event_UserCreated(string $platform, int $userId): void
    {
        self::Event('user.created', $platform, $userId);
    }

    public static function Event_WalletAdded(string $platform, int $userId, ?string $address): void
    {
        self::Event('wallet.added', $platform, $userId, $address);
    }

    public static function Event_WalletDeleted(int $userId, ?string $address): void
    {
        self::Event('wallet.deleted', null, $userId, $address);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function Event_RedirectDM_Activate(int $userId, string $message): void
    {
        self::Event('redirect-dm.activate', Config::kPLATFORM_Discord, $userId, $message);
    }

    public static function Event_RedirectDM_Deactivate(int $userId): void
    {
        self::Event('redirect-dm.deactivate', Config::kPLATFORM_Discord, $userId);
    }
}
