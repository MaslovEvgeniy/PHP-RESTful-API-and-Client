<?php

namespace app\components;

use PDO;

/**
 * Class PDOConnection
 * @package app\components
 */
class PDOConnection
{

    private static $connection;
    /**
     * Getting PDO connection
     * @return PDO connection with DB
     */
    public static function getConnection()
    {
        if (self::$connection === null) {
            $params = require(ROOT . "/config/db.php");

            $dsn = "mysql:host={$params['host']};dbname={$params['dbname']}";

            self::$connection = new PDO($dsn, $params['user'], $params['password']);

            self::$connection->exec("set names utf8");

            return self::$connection;
        }

        return self::$connection;
    }

    private function __clone(){}
    private function __construct(){}

}