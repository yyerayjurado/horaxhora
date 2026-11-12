<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class CategoriaDao
{
    // * Todas las categorías con cuántos servicios tiene cada una
    public function listar(): array
    {
        $categorias = [];

        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'SELECT c.id_categoria, c.nombre, c.descripcion,
                        COUNT(s.id_servicio) AS num_servicios
                 FROM categorias c
                 LEFT JOIN servicios s ON s.id_categoria = c.id_categoria
                 GROUP BY c.id_categoria
                 ORDER BY c.nombre'
            );
            $stmt->execute();

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categorias[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $categorias;
    }

    // * Crea una categoría y devuelve su id (o null si falla)
    public function crear(string $nombre, string $descripcion): ?int
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)');
            $stmt->execute([$nombre, $descripcion]);

            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function actualizar(int $id, string $nombre, string $descripcion): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('UPDATE categorias SET nombre = ?, descripcion = ? WHERE id_categoria = ?');

            return $stmt->execute([$nombre, $descripcion, $id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // ! Por el ON DELETE CASCADE, borrar una categoría borra sus servicios y las ofertas de esos servicios
    public function borrar(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('DELETE FROM categorias WHERE id_categoria = ?');

            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
