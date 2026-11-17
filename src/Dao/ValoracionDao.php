<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class ValoracionDao
{
    // * Todas las reseñas: quién la escribe, a quién valora y de qué servicio
    // * $puntuacion: null = todas, 1-5 = solo las de esas estrellas
    public function listar(?int $puntuacion): array
    {
        $resenas = [];

        // * "cli" es quien escribe la reseña (el cliente) y "pro" quien recibe la valoración
        $sql = 'SELECT v.id_valoracion, v.puntuacion, v.comentario, v.fecha_valoracion,
                       s.nombre AS servicio,
                       cli.nombre AS autor_nombre, cli.apellidos AS autor_apellidos,
                       pro.nombre AS valorado_nombre, pro.apellidos AS valorado_apellidos
                FROM valoraciones v
                JOIN solicitudes_trueque st ON st.id_solicitud = v.id_solicitud
                JOIN ofertas_profesionales o ON o.id_oferta = st.id_oferta
                JOIN servicios s ON s.id_servicio = o.id_servicio
                JOIN usuarios cli ON cli.id_usuario = st.id_cliente
                JOIN usuarios pro ON pro.id_usuario = o.id_profesional';

        $parametros = [];

        if ($puntuacion !== null) {
            $sql = $sql . ' WHERE v.puntuacion = ?';
            $parametros[] = $puntuacion;
        }

        $sql = $sql . ' ORDER BY v.fecha_valoracion DESC';

        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($parametros);

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $resenas[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $resenas;
    }

    // * Números para las tarjetas de resumen del panel
    public function resumen(): array
    {
        try {
            $pdo = BD::getConexion();
            // ! COALESCE pone 0 si no hay reseñas (si no, SUM devuelve NULL)
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) AS total,
                        ROUND(AVG(puntuacion), 1) AS media,
                        COALESCE(SUM(puntuacion <= 2), 0) AS bajas
                 FROM valoraciones'
            );
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total' => (int) $fila['total'],
                // * Si no hay reseñas, la media es null (el JS pinta "—")
                'media' => $fila['media'] !== null ? (float) $fila['media'] : null,
                'bajas' => (int) $fila['bajas'],
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['total' => 0, 'media' => null, 'bajas' => 0];
        }
    }

    // * Moderación suave: quita el texto pero deja las estrellas (la media no cambia)
    public function quitarComentario(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                'UPDATE valoraciones SET comentario = NULL
                 WHERE id_valoracion = ? AND comentario IS NOT NULL'
            );
            $stmt->execute([$id]);

            // * Si no ha cambiado nada es que no existía o ya no tenía comentario
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // ! Borra la reseña entera: la media del profesional se recalcula sin ella
    public function borrar(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare('DELETE FROM valoraciones WHERE id_valoracion = ?');
            $stmt->execute([$id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
