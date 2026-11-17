<?php

declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class TruequeDao
{
    // * Historial de trueques: quién contrata, a quién, qué servicio y cómo va
    // * $estado: null = todos
    public function listar(?string $estado): array
    {
        $trueques = [];

        // * Unimos usuarios dos veces: "cli" es quien contrata y "pro" quien presta el servicio
        $sql = 'SELECT st.id_solicitud, st.horas_estimadas, st.puntos_totales, st.fecha_propuesta,
                       st.estado, st.confirmado_cliente, st.confirmado_prestador, st.fecha_creacion,
                       s.nombre AS servicio,
                       cli.nombre AS cliente_nombre, cli.apellidos AS cliente_apellidos,
                       pro.nombre AS prestador_nombre, pro.apellidos AS prestador_apellidos
                FROM solicitudes_trueque st
                JOIN ofertas_profesionales o ON o.id_oferta = st.id_oferta
                JOIN servicios s ON s.id_servicio = o.id_servicio
                JOIN usuarios cli ON cli.id_usuario = st.id_cliente
                JOIN usuarios pro ON pro.id_usuario = o.id_profesional';

        $parametros = [];

        if ($estado !== null) {
            $sql = $sql . ' WHERE st.estado = ?';
            $parametros[] = $estado;
        }

        $sql = $sql . ' ORDER BY st.fecha_creacion DESC';

        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($parametros);

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $trueques[] = $fila;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }

        return $trueques;
    }

    // * Números para las tarjetas de resumen del panel
    public function resumen(): array
    {
        try {
            $pdo = BD::getConexion();
            // ? En MySQL, SUM(condición) cuenta cuántas filas la cumplen (true = 1, false = 0)
            // ! COALESCE pone 0 si la tabla está vacía (si no, SUM devuelve NULL)
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) AS total,
                        COALESCE(SUM(estado = 'COMPLETADO'), 0) AS completados,
                        COALESCE(SUM(estado IN ('SOLICITADO', 'ACEPTADO', 'EN_EJECUCION')), 0) AS en_curso,
                        COALESCE(SUM(CASE WHEN estado = 'COMPLETADO' THEN puntos_totales ELSE 0 END), 0) AS puntos_intercambiados
                 FROM solicitudes_trueque"
            );
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total' => (int)$fila['total'],
                'completados' => (int)$fila['completados'],
                'en_curso' => (int)$fila['en_curso'],
                'puntos_intercambiados' => (int)$fila['puntos_intercambiados'],
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['total' => 0, 'completados' => 0, 'en_curso' => 0, 'puntos_intercambiados' => 0];
        }
    }

    // * Cancela un trueque. Devuelve true si se ha cancelado
    // ! El WHERE con el estado impide cancelar uno COMPLETADO (sus puntos ya se transfirieron)
    public function cancelar(int $id): bool
    {
        try {
            $pdo = BD::getConexion();
            $stmt = $pdo->prepare(
                "UPDATE solicitudes_trueque
                 SET estado = 'CANCELADO'
                 WHERE id_solicitud = ?
                   AND estado IN ('SOLICITADO', 'ACEPTADO', 'EN_EJECUCION')"
            );
            $stmt->execute([$id]);

            // * Si no ha cambiado ninguna fila, es que no existía o ya estaba terminado
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
