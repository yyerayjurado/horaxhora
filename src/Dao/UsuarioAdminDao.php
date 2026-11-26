<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class UsuarioAdminDao
{
    // * Usuarios ya aprobados, los más nuevos primero. $busqueda filtra por nombre, apellidos o email
    // ! Los pendientes no salen aquí: tienen su propia lista (listarPendientes)
    public function listar(string $busqueda): array
    {
        $usuarios = [];

        $sql = 'SELECT id_usuario, nombre, apellidos, email, telefono, rol, puntos_saldo, fecha_registro, activo
                FROM usuarios
                WHERE aprobado = TRUE';
        $parametros = [];

        if ($busqueda !== '') {
            $sql = $sql . ' AND (nombre LIKE ? OR apellidos LIKE ? OR email LIKE ?)';
            $parametros[] = '%' . $busqueda . '%';
            $parametros[] = '%' . $busqueda . '%';
            $parametros[] = '%' . $busqueda . '%';
        }

        $sql = $sql . ' ORDER BY fecha_registro DESC';

        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($parametros);

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $usuarios;
    }

    // * Registros que esperan a que el admin los acepte, con los servicios que eligieron
    // ! Los servicios salen de servicios_profesional (en el registro ya NO se crean ofertas)
    public function listarPendientes(): array
    {
        $pendientes = [];

        try {
            $pdo = BD::getConexion();
            // ? GROUP_CONCAT junta en un texto los nombres de sus servicios: "Clases de inglés, Clases de francés"
            $stmt = $pdo->prepare(
                "SELECT u.id_usuario, u.nombre, u.apellidos, u.email, u.telefono, u.fecha_registro,
                        GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ', ') AS servicios
                 FROM usuarios u
                 LEFT JOIN servicios_profesional sp ON sp.id_profesional = u.id_usuario
                 LEFT JOIN servicios s ON s.id_servicio = sp.id_servicio
                 WHERE u.aprobado = FALSE
                 GROUP BY u.id_usuario
                 ORDER BY u.fecha_registro ASC"
            );
            $stmt->execute();

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pendientes[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $pendientes;
    }

    // * Cuántos hay pendientes (para el contador de la pestaña)
    public function contarPendientes(): int
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM usuarios WHERE aprobado = FALSE');
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int) $fila['total'];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    // * Nombre y email de un usuario (para enviarle correos)
    public function obtenerContacto(int $id): ?array
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('SELECT nombre, apellidos, email FROM usuarios WHERE id_usuario = ?');
            $stmt->execute([$id]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila ?: null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // * Lo usa el registro público: la cuenta nueva queda esperando al admin
    public function marcarPendiente(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('UPDATE usuarios SET aprobado = FALSE WHERE id_usuario = ?');

            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * El admin acepta el registro
    public function aprobar(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('UPDATE usuarios SET aprobado = TRUE WHERE id_usuario = ? AND aprobado = FALSE');
            $stmt->execute([$id]);

            // * Si no cambia nada es que no existía o ya estaba aprobado
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * El admin rechaza el registro: se borra la cuenta (aún no tiene historial)
    // ! El "AND aprobado = FALSE" impide borrar por aquí a un usuario que ya estaba aprobado
    public function rechazar(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id_usuario = ? AND aprobado = FALSE');
            $stmt->execute([$id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * Crea un profesional desde el panel (entra ya aprobado: el valor por defecto de la BD es TRUE)
    public function crear(string $nombre, string $apellidos, string $email, string $hash, ?string $telefono, int $puntos): ?int
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombre, apellidos, email, password, telefono, puntos_saldo) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$nombre, $apellidos, $email, $hash, $telefono, $puntos]);

            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function actualizar(int $id, string $nombre, string $apellidos, string $email, ?string $telefono, int $puntos): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'UPDATE usuarios
                 SET nombre = ?, apellidos = ?, email = ?, telefono = ?, puntos_saldo = ?
                 WHERE id_usuario = ?'
            );

            return $stmt->execute([$nombre, $apellidos, $email, $telefono, $puntos, $id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * Dar de baja (false) o reactivar (true)
    public function cambiarActivo(int $id, bool $activo): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('UPDATE usuarios SET activo = ? WHERE id_usuario = ?');

            return $stmt->execute([$activo ? 1 : 0, $id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // ! Borra de verdad: por el ON DELETE CASCADE se van sus ofertas, solicitudes y valoraciones
    public function borrar(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id_usuario = ?');

            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * Para el login: ¿está activo (no dado de baja)?
    public function estaActivo(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('SELECT activo FROM usuarios WHERE id_usuario = ?');
            $stmt->execute([$id]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila !== false && (int) $fila['activo'] === 1;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * Para el login: ¿le ha aprobado ya el admin?
    public function estaAprobado(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('SELECT aprobado FROM usuarios WHERE id_usuario = ?');
            $stmt->execute([$id]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila !== false && (int) $fila['aprobado'] === 1;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * ¿Hay otro usuario con este email? $excluirId: al editar, no contamos al propio usuario
    public function emailEnUso(string $email, ?int $excluirId): bool
    {
        try {
            $pdo = BD::getConexion();

            if ($excluirId === null) {
                $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = ?');
                $stmt->execute([$email]);
            } else {
                $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?');
                $stmt->execute([$email, $excluirId]);
            }

            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return true; // ! Ante la duda, decimos que sí para no crear duplicados
        }
    }
}
