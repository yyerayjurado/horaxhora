<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;
use RuntimeException;

/**
 * * Conexión única a la base de datos (patrón Singleton).
 * * Todos los DAO piden la conexión con BD::getConexion() y comparten el mismo objeto PDO.
 */
final class BD
{
    private static ?PDO $conexion = null;

    // ! Constructor privado: nadie puede hacer "new BD()"
    private function __construct()
    {
    }

    public static function getConexion(): PDO
    {
        // * Solo se conecta la primera vez; después reutiliza la misma conexión
        if (self::$conexion === null) {
            // * Los datos vienen de las variables "environment" del docker-compose.yml
            $host    = getenv('DB_HOST') ?: 'db';
            $nombre  = getenv('DB_NAME') ?: 'sistema_trueque';
            $usuario = getenv('DB_USER') ?: 'usuario';
            $clave   = getenv('DB_PASS') ?: 'usuario';

            $dsn = "mysql:host={$host};dbname={$nombre};charset=utf8mb4";

            try {
                self::$conexion = new PDO($dsn, $usuario, $clave, [
                    // * Si una consulta falla, lanza una excepción en vez de fallar en silencio
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // * Los resultados vienen como arrays asociativos: $fila['nombre']
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // * Consultas preparadas reales en MySQL (protege contra inyección SQL)
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // ! No mostramos el mensaje original: puede incluir usuario o host
                throw new RuntimeException('No se ha podido conectar con la base de datos.', 0, $e);
            }
        }

        return self::$conexion;
    }
}