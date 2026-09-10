<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function connection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        /** @var array{dsn:string,username:string,password:string,options:array} $config */
        $config = require dirname(__DIR__) . '/config/database.php';

        try {
            self::$instance = new PDO(
                $config['dsn'],
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            throw new RuntimeException('No se pudo conectar a la base de datos.', 0, $e);
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
