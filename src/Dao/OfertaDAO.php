<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class OfertaDao
{
    // * Busca las ofertas activas para el buscador de servicios
    // * $texto: lo que escribe el usuario (busca en servicio, descripción y nombre del profesional)
    // * $idCategoria: null = todas las categorías
    // * $orden: 'valoracion', 'precio_asc' o 'precio_desc'
    public function buscar(string $texto, ?int $idCategoria, string $orden): array
    {
        $ofertas = [];

        try {
            $pdo = BD::getConexion();

            // * La valoración media sale de las valoraciones de las solicitudes de cada oferta
            // * LEFT JOIN para que también salgan las ofertas que aún no tienen valoraciones
            $sql = 'SELECT o.id_oferta, o.puntos_hora, o.descripcion_personal,
                           s.nombre AS servicio,
                           c.id_categoria, c.nombre AS categoria,
                           u.id_usuario AS id_profesional, u.nombre AS profesional,
                           ROUND(AVG(v.puntuacion), 1) AS media_valoracion,
                           COUNT(v.id_valoracion) AS num_valoraciones
                    FROM ofertas_profesionales o
                    JOIN servicios s ON s.id_servicio = o.id_servicio
                    JOIN categorias c ON c.id_categoria = s.id_categoria
                    JOIN usuarios u ON u.id_usuario = o.id_profesional
                    LEFT JOIN solicitudes_trueque st ON st.id_oferta = o.id_oferta
                    LEFT JOIN valoraciones v ON v.id_solicitud = st.id_solicitud
                    WHERE o.activo = TRUE';

            $parametros = [];

            // * Solo se añade el filtro si el usuario ha escrito algo
            if ($texto !== '') {
                $sql .= ' AND (s.nombre LIKE ? OR o.descripcion_personal LIKE ? OR u.nombre LIKE ?)';
                $parametros[] = "%{$texto}%";
                $parametros[] = "%{$texto}%";
                $parametros[] = "%{$texto}%";
            }

            if ($idCategoria !== null) {
                $sql .= ' AND c.id_categoria = ?';
                $parametros[] = $idCategoria;
            }

            $sql .= ' GROUP BY o.id_oferta, o.puntos_hora, o.descripcion_personal, s.nombre,
                               c.id_categoria, c.nombre, u.id_usuario, u.nombre';

            // ! El orden NO va con "?": se elige de una lista fija para evitar inyección SQL
            switch ($orden) {
                case 'precio_asc':
                    $sql .= ' ORDER BY o.puntos_hora ASC';
                    break;
                case 'precio_desc':
                    $sql .= ' ORDER BY o.puntos_hora DESC';
                    break;
                default:
                    // * Mejor valorados primero; los que no tienen valoraciones, al final
                    $sql .= ' ORDER BY AVG(v.puntuacion) IS NULL, AVG(v.puntuacion) DESC';
                    break;
            }

            // ! prepare() porque el texto y la categoría vienen del usuario
            $stmt = $pdo->prepare($sql);
            $stmt->execute($parametros);

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ofertas[] = $fila;
            }
        } catch (PDOException $e) {
            echo 'Error en la consulta: ' . $e->getMessage();
        }

        return $ofertas;
    }
}
