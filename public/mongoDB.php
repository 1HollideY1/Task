<?php
require '../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\AuthenticationException;

class DatabaseConnection {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            try {
                self::$connection = new Client(
                    "mongodb://me:qwe123@mongodb:27017"
                );
            } catch (Exception $e) {
                die("Ошибка подключения к mongoDB: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}