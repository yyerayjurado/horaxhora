<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class PerfilDAO
{
    private PDO $conexion;

    public function __construct()
    {
        // * Datos sacados del docker-compose.yml (servicio "app" → environment)
        $host     = getenv('DB_HOST') ?: 'db';
        $nombreBD = getenv('DB_NAME') ?: 'sistema_trueque';
        $usuario  = getenv('DB_USER') ?: 'usuario';
        $password = getenv('DB_PASS') ?: 'usuario';

        try {
            $this->conexion = new PDO(
                "mysql:host=$host;dbname=$nombreBD;charset=utf8mb4",
                $usuario,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die('Error de conexión con la base de datos');
        }
    }

    public function obtenerUsuario(int $id): ?array
    {
        try {
            $stmt = $this->conexion->prepare(
                'SELECT id_usuario, nombre, apellidos, email, telefono, rol, puntos_saldo, fecha_registro
                 FROM usuarios WHERE id_usuario = ?'
            );
            $stmt->execute([$id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            return $usuario ?: null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function obtenerOfertas(int $idProfesional): array
    {
        try {
            $stmt = $this->conexion->prepare(
                'SELECT o.id_oferta, o.puntos_hora, o.descripcion_personal, o.activo,
                        s.nombre AS nombre_servicio, s.puntos_hora_referencia,
                        c.nombre AS nombre_categoria
                 FROM ofertas_profesionales o
                 JOIN servicios s ON s.id_servicio = o.id_servicio
                 JOIN categorias c ON c.id_categoria = s.id_categoria
                 WHERE o.id_profesional = ?
                 ORDER BY s.nombre'
            );
            $stmt->execute([$idProfesional]);

            $ofertas = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ofertas[] = $fila;
            }
            return $ofertas;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // * Solo servicios que el profesional aún NO oferta (por el UNIQUE de la tabla)
    public function obtenerServiciosDisponibles(int $idProfesional): array
    {
        try {
            $stmt = $this->conexion->prepare(
                'SELECT id_servicio, nombre, puntos_hora_referencia
                 FROM servicios
                 WHERE id_servicio NOT IN (
                     SELECT id_servicio FROM ofertas_profesionales WHERE id_profesional = ?
                 )
                 ORDER BY nombre'
            );
            $stmt->execute([$idProfesional]);

            $servicios = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $servicios[] = $fila;
            }
            return $servicios;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // * Media y número de reseñas recibidas como prestador
    public function obtenerValoracion(int $idProfesional): array
    {
        try {
            $stmt = $this->conexion->prepare(
                'SELECT AVG(v.puntuacion) AS media, COUNT(v.id_valoracion) AS total
                 FROM valoraciones v
                 JOIN solicitudes_trueque st ON st.id_solicitud = v.id_solicitud
                 JOIN ofertas_profesionales o ON o.id_oferta = st.id_oferta
                 WHERE o.id_profesional = ?'
            );
            $stmt->execute([$idProfesional]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function actualizarUsuario(int $id, string $nombre, string $apellidos, string $email, string $telefono): bool
    {
        try {
            $stmt = $this->conexion->prepare(
                'UPDATE usuarios SET nombre = ?, apellidos = ?, email = ?, telefono = ?
                 WHERE id_usuario = ?'
            );
            return $stmt->execute([$nombre, $apellidos, $email, $telefono, $id]);
        } catch (PDOException $e) {
            // ! Salta aquí si el email ya existe (UNIQUE)
            error_log($e->getMessage());
            return false;
        }
    }
}
