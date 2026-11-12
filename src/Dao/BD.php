<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;
use RuntimeException;

/**
 * * Conexión única a la base de datos (patrón Singleton).
 */
final class BD
{
    private static ?PDO $conexion = null;

    private function __construct()
    {
    }

    public static function getConexion(): PDO
    {
        if (self::$conexion === null) {
            $host    = getenv('DB_HOST') ?: 'db';
            $nombre  = getenv('DB_NAME') ?: 'sistema_trueque';
            $usuario = getenv('DB_USER') ?: 'usuario';
            $clave   = getenv('DB_PASS') ?: 'usuario';

            $dsn = "mysql:host={$host};dbname={$nombre};charset=utf8mb4";

            try {
                self::$conexion = new PDO($dsn, $usuario, $clave, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new RuntimeException('No se ha podido conectar con la base de datos.', 0, $e);
            }
        }

        return self::$conexion;
    }
}
