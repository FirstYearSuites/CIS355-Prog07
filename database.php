<?php
class Database
{
    /*
    // localhost local machine database
    private static $dbName = 'projects' ;
    private static $dbHost = '127.0.0.1' ;
    private static $dbUsername = 'root';
    private static $dbUserPassword = '';
    */
    // csis.svsu.edu database
    private static $dbName = 'bhfalor355wi19' ;
    private static $dbHost = '10.8.30.49' ;
    private static $dbUsername = 'bhfalor355wi19';
    private static $dbUserPassword = 'F4l0r355Pa$$';

    private static $cont  = null;

    public function __construct() {
        exit('Init function is not allowed');
    }

    public static function connect()
    {
        // One connection through whole application
        if ( null == self::$cont )
        {
            try
            {
                self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword);
            }
            catch(PDOException $e)
            {
                die($e->getMessage());
            }
        }
        return self::$cont;
    }

    public static function disconnect()
    {
        self::$cont = null;
    }
}
?>