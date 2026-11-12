<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class ServicioDao
{
    // * Todas las categorías, para el desplegable del registro
    public function obtenerCategorias(): array
    {
        $categorias = [];

        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('SELECT id_categoria, nombre FROM categorias ORDER BY nombre');
            $stmt->execute();

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categorias[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $categorias;
    }

    // * Servicios del catálogo que pertenecen a una categoría (registro)
    public function obtenerPorCategoria(int $idCategoria): array
    {
        $servicios = [];

        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'SELECT id_servicio, nombre, puntos_hora_referencia
                 FROM servicios
                 WHERE id_categoria = ?
                 ORDER BY nombre'
            );
            $stmt->execute([$idCategoria]);

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $servicios[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $servicios;
    }

    // * Comprueba que un servicio existe en el catálogo
    public function existe(int $idServicio): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('SELECT id_servicio FROM servicios WHERE id_servicio = ?');
            $stmt->execute([$idServicio]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila !== false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * ===== Servicios que puede ofertar cada profesional =====

    // * Registro: guarda los servicios que ha marcado (NO crea ofertas)
    public function guardarDeProfesional(int $idProfesional, array $idsServicios): void
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('INSERT INTO servicios_profesional (id_profesional, id_servicio) VALUES (?, ?)');

            foreach ($idsServicios as $idServicio) {
                $stmt->execute([$idProfesional, $idServicio]);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }

    // * Crear oferta: sus servicios que TODAVÍA no tiene ofertados (los únicos que puede elegir)
    public function obtenerOfertablesDeProfesional(int $idProfesional): array
    {
        $servicios = [];

        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'SELECT s.id_servicio, s.nombre, s.puntos_hora_referencia, c.nombre AS categoria
                 FROM servicios_profesional sp
                 JOIN servicios s ON s.id_servicio = sp.id_servicio
                 JOIN categorias c ON c.id_categoria = s.id_categoria
                 WHERE sp.id_profesional = ?
                   AND s.id_servicio NOT IN (
                       SELECT id_servicio FROM ofertas_profesionales WHERE id_profesional = ?
                   )
                 ORDER BY s.nombre'
            );
            $stmt->execute([$idProfesional, $idProfesional]);

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $servicios[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $servicios;
    }

    // ! Crear oferta (PHP): comprueba que este servicio es de los suyos, aunque el JS ya lo filtre
    public function puedeOfertar(int $idProfesional, int $idServicio): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'SELECT id_servicio FROM servicios_profesional WHERE id_profesional = ? AND id_servicio = ?'
            );
            $stmt->execute([$idProfesional, $idServicio]);

            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // * ===== Métodos del panel de administración =====

    // * Todos los servicios con su categoría y cuántas ofertas tiene cada uno
    // * $idCategoria: null = todas las categorías
    public function listarTodos(?int $idCategoria): array
    {
        $servicios = [];

        $sql = 'SELECT s.id_servicio, s.id_categoria, s.nombre, s.descripcion, s.puntos_hora_referencia,
                       c.nombre AS categoria,
                       COUNT(o.id_oferta) AS num_ofertas
                FROM servicios s
                JOIN categorias c ON c.id_categoria = s.id_categoria
                LEFT JOIN ofertas_profesionales o ON o.id_servicio = s.id_servicio';

        $parametros = [];

        // * Solo filtramos si ha elegido una categoría
        if ($idCategoria !== null) {
            $sql = $sql . ' WHERE s.id_categoria = ?';
            $parametros[] = $idCategoria;
        }

        $sql = $sql . ' GROUP BY s.id_servicio ORDER BY c.nombre, s.nombre';

        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($parametros);

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $servicios[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $servicios;
    }

    // * Crea un servicio y devuelve su id (o null si falla)
    public function crear(int $idCategoria, string $nombre, string $descripcion, int $puntos): ?int
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'INSERT INTO servicios (id_categoria, nombre, descripcion, puntos_hora_referencia)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$idCategoria, $nombre, $descripcion, $puntos]);

            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            // ! Salta aquí si la categoría no existe (clave foránea)
            error_log($e->getMessage());
            return null;
        }
    }

    public function actualizar(int $id, int $idCategoria, string $nombre, string $descripcion, int $puntos): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'UPDATE servicios
                 SET id_categoria = ?, nombre = ?, descripcion = ?, puntos_hora_referencia = ?
                 WHERE id_servicio = ?'
            );

            return $stmt->execute([$idCategoria, $nombre, $descripcion, $puntos, $id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // ! Por el ON DELETE CASCADE se borran también sus ofertas, solicitudes y valoraciones
    public function borrar(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('DELETE FROM servicios WHERE id_servicio = ?');

            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
