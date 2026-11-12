<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class OfertaDao
{
    // * Busca las ofertas para el buscador de servicios
    public function buscar(string $texto, ?int $idCategoria, string $orden): array
    {
        $ofertas = [];

        // * 1. La consulta base: todas las ofertas activas con su servicio, categoría y profesional
        $sql = "SELECT o.id_oferta, o.puntos_hora, o.descripcion_personal,
                       s.nombre AS servicio,
                       c.id_categoria, c.nombre AS categoria,
                       u.id_usuario AS id_profesional, u.nombre AS profesional,
                       AVG(v.puntuacion) AS media_valoracion,
                       COUNT(v.id_valoracion) AS num_valoraciones
                FROM ofertas_profesionales o
                JOIN servicios s ON s.id_servicio = o.id_servicio
                JOIN categorias c ON c.id_categoria = s.id_categoria
                JOIN usuarios u ON u.id_usuario = o.id_profesional
                LEFT JOIN solicitudes_trueque st ON st.id_oferta = o.id_oferta
                LEFT JOIN valoraciones v ON v.id_solicitud = st.id_solicitud
                WHERE o.activo = TRUE";

        // * Aquí vamos guardando los valores que sustituyen a cada "?"
        $parametros = [];

        // * 2. Si el usuario ha escrito algo, filtramos por texto
        if ($texto != '') {
            $sql = $sql . " AND (s.nombre LIKE ? OR u.nombre LIKE ?)";
            $parametros[] = '%' . $texto . '%';
            $parametros[] = '%' . $texto . '%';
        }

        // * 3. Si ha elegido una categoría, filtramos por categoría
        if ($idCategoria != null) {
            $sql = $sql . " AND c.id_categoria = ?";
            $parametros[] = $idCategoria;
        }

        // * 4. Agrupamos por oferta para poder calcular la media de cada una
        $sql = $sql . " GROUP BY o.id_oferta";

        // * 5. Ordenamos según lo que haya elegido
        if ($orden == 'precio_asc') {
            $sql = $sql . " ORDER BY o.puntos_hora ASC";
        } else if ($orden == 'precio_desc') {
            $sql = $sql . " ORDER BY o.puntos_hora DESC";
        } else {
            $sql = $sql . " ORDER BY media_valoracion DESC";
        }

        // * 6. Ejecutamos la consulta y guardamos los resultados
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($parametros);

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ofertas[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $ofertas;
    }
}
