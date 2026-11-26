<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\OfertaDao;
use App\Dao\TruequeDao;
use App\Dao\UsuarioDao;
use DateTime;
use DateTimeZone;

// * Solicitar un servicio a otro profesional
// * POST /api/solicitudes  -> lo llama buscador.js desde el modal "Solicitar"
class SolicitudController
{
    public function crearApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // ! Solo puede solicitar quien ha iniciado sesión
        if (!isset($_SESSION['usuario'])) {
            $this->responderJson(401, ['ok' => false, 'error' => 'Tienes que iniciar sesión para solicitar un servicio']);
            return;
        }

        // ! El cliente SIEMPRE sale de la sesión, nunca de lo que mande el JS
        $idCliente = $_SESSION['usuario']['id'];

        // * Los datos llegan en el cuerpo de la petición como JSON
        $datos = json_decode(file_get_contents('php://input'), true) ?? [];

        $idOferta = (int) ($datos['id_oferta'] ?? 0);
        $fecha = trim((string) ($datos['fecha'] ?? ''));
        $hora = trim((string) ($datos['hora'] ?? ''));
        $horas = (float) ($datos['horas'] ?? 0);

        // * 1. Horas: entre 0,5 y 8, de media en media hora
        // ? fmod($horas * 2, 1) es 0 solo si $horas es 0.5, 1, 1.5, 2...
        if ($horas < 0.5 || $horas > 8 || fmod($horas * 2, 1) != 0) {
            $this->responderJson(400, ['ok' => false, 'error' => 'Las horas tienen que ir de media en media hora, entre 0,5 y 8']);
            return;
        }

        // * 2. Fecha y hora: que sea una fecha de verdad y que no haya pasado
        $zona = new DateTimeZone('Europe/Madrid');
        $fechaPropuesta = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $hora, $zona);

        // ! createFromFormat acepta cosas raras como "2026-02-31": comparando con format() las descartamos
        if ($fechaPropuesta === false || $fechaPropuesta->format('Y-m-d H:i') !== $fecha . ' ' . $hora) {
            $this->responderJson(400, ['ok' => false, 'error' => 'La fecha o la hora no son válidas']);
            return;
        }

        if ($fechaPropuesta <= new DateTime('now', $zona)) {
            $this->responderJson(400, ['ok' => false, 'error' => 'La fecha tiene que ser posterior a ahora']);
            return;
        }

        // * 3. La oferta tiene que existir y estar disponible
        $oferta = (new OfertaDao())->buscarDisponible($idOferta);

        if ($oferta === null) {
            $this->responderJson(404, ['ok' => false, 'error' => 'Esta oferta ya no está disponible']);
            return;
        }

        // ! No te puedes contratar a ti mismo
        if ($oferta['id_profesional'] == $idCliente) {
            $this->responderJson(400, ['ok' => false, 'error' => 'No puedes solicitar tu propia oferta']);
            return;
        }

        // * 4. Puntos: los calcula el servidor con el precio de la BD (el JS no puede trucarlo)
        $puntosTotales = (int) round($oferta['puntos_hora'] * $horas);

        // ? Los puntos no se quitan ahora (solo al completarse), pero si ya no le llegan no le dejamos pedir
        $saldo = (new UsuarioDao())->obtenerSaldo($idCliente);

        if ($saldo < $puntosTotales) {
            $this->responderJson(400, [
                'ok' => false,
                'error' => "No tienes puntos suficientes: necesitas $puntosTotales y tienes $saldo"
            ]);
            return;
        }

        // * 5. Guardamos la solicitud
        $idSolicitud = (new TruequeDao())->crear(
            $idCliente,
            $idOferta,
            $horas,
            $puntosTotales,
            $fechaPropuesta->format('Y-m-d H:i:s')
        );

        if ($idSolicitud === null) {
            $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido enviar la solicitud']);
            return;
        }

        $this->responderJson(201, ['ok' => true, 'id_solicitud' => $idSolicitud, 'puntos_totales' => $puntosTotales]);
    }

    private function responderJson(int $codigo, array $respuesta): void
    {
        http_response_code($codigo);
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }
}
