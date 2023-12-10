<?php
class Connection
{
    private static $pdoInstance;

    public static function connect()
    {
        $host = "localhost";
        $user = "root";
        $password = "";
        $db = "cart";
        $charset = "utf8mb4";

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        try {
            self::$pdoInstance = new \PDO($dsn, $user, $password);
            self::$pdoInstance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        return self::$pdoInstance;
    }
}
