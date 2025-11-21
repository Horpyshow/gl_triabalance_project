<?php
class DB {
    private static $instance = null;

    public static function conn() {
        if (self::$instance === null) {
            $host = "localhost";
            $db = "wealth_creation";
            $user = "root";
            $pass = "";

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8";

            try {
                self::$instance = new PDO($dsn, $user, $pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("DB Error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
?>
