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

    // * Servicios del catálogo que pertenecen a una categoría
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
}
