<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = (string) config('db.host');
        $port = (int) config('db.port', 3306);
        $database = (string) config('db.database');
        $username = (string) config('db.username');
        $password = (string) config('db.password');
        $charset = (string) config('db.charset', 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            http_response_code(500);
            echo 'No se pudo conectar a la base de datos. Revisa config/config.php';
            if ((bool) config('app.debug', false)) {
                echo '<pre>' . e($exception->getMessage()) . '</pre>';
            }
            exit;
        }

        return self::$connection;
    }
}
