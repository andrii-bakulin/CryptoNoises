<?php

namespace CryptoCore\Vss;

use Base;

class Vss
{
    private static ?Base $f3Instance = null;

    public static function initialize()
    {
        // Fat-Free itself sets the HTTP status and writes the error with trace to error_log before calling ONERROR.
        // This handler only suppresses F3's default error page, so no error details are sent to the client.
        Vss::f3()->set('ONERROR', function (Base $f3)
        {
        });
    }

    public static function f3(): Base
    {
        if (self::$f3Instance === null)
            self::$f3Instance = Base::instance();

        return self::$f3Instance;
    }

    //------------------------------------------------------------------------------------------------------------------

    /** @var VssDb[] $DbInstances */
    private static array $DbInstances = [];

    public static function dbc(string $dbId, $charset = null): VssDb
    {
        $dbId_ext = $dbId . '_charset_' . $charset;

        if (!isset(self::$DbInstances[$dbId_ext]))
            self::$DbInstances[$dbId_ext] = new VssDb($dbId, $charset);

        return self::$DbInstances[$dbId_ext];
    }

    public static function dbCloseAll()
    {
        foreach (self::$DbInstances as $db)
        {
            $db->close();
        }
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function db(): VssDb
    {
        return self::dbc('dbMain', 'utf8mb4');
    }

    public static function dbShared(): VssDb
    {
        return self::dbc('dbShared', 'utf8mb4');
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function dbLog(): VssDb
    {
        return self::dbc('dbLog', 'utf8mb4');
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function dbTezos(): VssDb
    {
        return self::dbc('dbTezos', 'utf8mb4');
    }

    public static function dbTezosLog(): VssDb
    {
        return self::dbc('dbTezosLog', 'utf8mb4');
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function request(): VssRequest
    {
        static $instance = null;

        if ($instance == null)
            $instance = new VssRequest();

        return $instance;
    }
}
