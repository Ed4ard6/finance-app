<?php

namespace App\Core;


use PDO;
use PDOException;


class Database
{
    private static ?PDO $pdo = null;


    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../Config/config.php';


            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $config['db_host'],
                $config['db_name']
            );


            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];


            try {
                self::$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
            } catch (PDOException $e) {
                // En producción podrías loguear y mostrar una página amigable
                die('Error de conexión DB: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
