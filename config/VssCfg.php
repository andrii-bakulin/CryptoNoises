<?php

use CryptoCore\Vss\Vss;

require_once(dirname(__FILE__).'/consts.php');

class VssCfg
{
    private static ?VssCfg $instance = null;

    public string $pathTopCore;
    public string $pathLog;

    //------------------------------------------------------------------------------------------------------------------

    public static function cfg(): VssCfg
    {
        if( self::$instance === null )
            self::$instance = new VssCfg();

        return self::$instance;
    }

    public function __construct()
    {
        $this->pathTopCore                  = dirname(__FILE__).'/..';
        $this->pathLog                      = $this->pathTopCore.'/log';

        ini_set('display_errors',           false );
        ini_set('error_reporting',          E_ALL & ~E_NOTICE );
        $this->setErrorReportingScope(null);

        mb_internal_encoding( 'UTF-8' );
        mb_regex_encoding( 'UTF-8' );

        setlocale( LC_ALL,                  'en_US.UTF-8' );
        setlocale( LC_NUMERIC,              'en_US.UTF-8' );

        ini_set('max_execution_time',       28 );
        ini_set('max_input_time',           28 );
        set_time_limit(28);

        mt_srand();
    }

    //------------------------------------------------------------------------------------------------------------------

    private array $errorScopesStack = [];

    public function setErrorReportingScope(?string $scope): void
    {
        if (empty($scope))
            $scope = date('Ymd');

        $this->errorScopesStack = [$scope];

        $this->applyErrorReportingScope();
    }

    public function pushErrorReportingScope(string $scopeSuffix): void
    {
        $this->errorScopesStack[] = $scopeSuffix;

        $this->applyErrorReportingScope();
    }

    public function popErrorReportingScope(): void
    {
        if (!empty($this->errorScopesStack))
            array_pop($this->errorScopesStack);

        $this->applyErrorReportingScope();
    }

    private function applyErrorReportingScope(): void
    {
        ini_set('error_log',  $this->pathLog.'/php_error.'.join('.', $this->errorScopesStack).'.log');
    }

    //------------------------------------------------------------------------------------------------------------------

    public function initialize()
    {
        Vss::f3()->set( 'TZ', 'Etc/UTC' );
    }

    /**
     * Database name from the connection DSN (`...;dbname=<name>`), for cross-database queries.
     */
    public function getDbName(string $connectionId): string
    {
        $dsn = $this->getDbConfig($connectionId)['connectionString'];

        if (preg_match('/(?:^|[:;])dbname=([^;]+)/', $dsn, $m) == 0)
            die('VssCfg::getDbName: no dbname in DSN of ['.$connectionId.']');

        return $m[1];
    }

    public function getDbConfig( $connectionId )
    {
        switch ($connectionId)
        {
            case 'dbMain':
                return array(
                    'connectionString'  => kSECRET_MYSQL_HOST_MAIN,
                    'username'          => kSECRET_MYSQL_USER,
                    'password'          => kSECRET_MYSQL_PSWD,
                );

            case 'dbShared':
                return array(
                    'connectionString'  => kSECRET_MYSQL_HOST_SHARED,
                    'username'          => kSECRET_MYSQL_USER,
                    'password'          => kSECRET_MYSQL_PSWD,
                );

            case 'dbLog':
                return array(
                    'connectionString'  => kSECRET_MYSQL_HOST_LOG,
                    'username'          => kSECRET_MYSQL_USER,
                    'password'          => kSECRET_MYSQL_PSWD,
                );

            //----------------------------------------------------------------------------------------------------------

            case 'dbTezos':
                return array(
                    'connectionString'  => kSECRET_TEZOS_MYSQL_HOST_MAIN,
                    'username'          => kSECRET_TEZOS_MYSQL_USER,
                    'password'          => kSECRET_TEZOS_MYSQL_PSWD,
                );

            case 'dbTezosLog':
                return array(
                    'connectionString'  => kSECRET_TEZOS_MYSQL_HOST_LOG,
                    'username'          => defined('kSECRET_TEZOS_MYSQL_USER_LOG') ? kSECRET_TEZOS_MYSQL_USER_LOG : kSECRET_TEZOS_MYSQL_USER,
                    'password'          => defined('kSECRET_TEZOS_MYSQL_PSWD_LOG') ? kSECRET_TEZOS_MYSQL_PSWD_LOG : kSECRET_TEZOS_MYSQL_PSWD,
                );
        }

        die('VssCfg::getDbConfig: Undefined db-key ['.$connectionId.']');
    }
}

VssCfg::cfg(); // auto-initialize
