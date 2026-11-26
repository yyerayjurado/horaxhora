<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\OfertaDao;
use App\Dao\ServicioDao;
use Twig\Environment;

class OfertaController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    // * GET /api/ofertas?busqueda=...&categoria=...&orden=...
    // * Lo llama buscador.js y devuelve las ofertas en JSON
    public function listarApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $texto = trim($_GET['busqueda'] ?? '');
        $categoria = $_GET['categoria'] ?? '';
        $orden = $_GET['orden'] ?? 'valoracion';

        // * Categoría vacía = todas
        $idCategoria = null;
        if ($categoria != '') {
            $idCategoria = (int) $categoria;
        }

        $ofertas = (new OfertaDao())->buscar($texto, $idCategoria, $orden);

        echo json_encode(['ok' => true, 'ofertas' => $ofertas], JSON_UNESCAPED_UNICODE);
    }

    // * GET /ofertas/crear: la página (los datos los pide crear-oferta.js)
    public function paginaCrear(): void
    {
        // ! Sin sesión, al login
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit();
        }

        echo $this->twig->render('pages/crearoferta.html.twig', ['pagina_actual' => 'crear-oferta']);
    }

    // * GET /api/mis-ofertas: los servicios que puedo ofertar y las ofertas que ya tengo
    public function misOfertasApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['usuario'])) {
            $this->responderJson(401, ['ok' => false, 'error' => 'Tienes que iniciar sesión']);
            return;
        }

        $idUsuario = $_SESSION['usuario']['id'];

        $this->responderJson(200, [
            'ok' => true,
            'ofertables' => (new ServicioDao())->obtenerOfertablesDeProfesional($idUsuario),
            'ofertas' => (new OfertaDao())->deProfesional($idUsuario)
        ]);
    }

    // * POST /api/ofertas: crea una oferta nueva
    public function crearApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['usuario'])) {
            $this->responderJson(401, ['ok' => false, 'error' => 'Tienes que iniciar sesión']);
            return;
        }

        // ! El profesional sale de la sesión, nunca de lo que mande el JS
        $idUsuario = $_SESSION['usuario']['id'];

        // * Los datos llegan como JSON
        $datos = json_decode(file_get_contents('php://input'), true) ?? [];

        $idServicio = (int) ($datos['id_servicio'] ?? 0);
        $puntos = (int) ($datos['puntos_hora'] ?? 0);
        $descripcion = trim($datos['descripcion'] ?? '');

        // * 1. Buscamos el servicio entre los que puede ofertar (los suyos que aún no tiene ofertados)
        $servicio = null;
        $ofertables = (new ServicioDao())->obtenerOfertablesDeProfesional($idUsuario);

        foreach ($ofertables as $ofertable) {
            if ($ofertable['id_servicio'] == $idServicio) {
                $servicio = $ofertable;
            }
        }

        if ($servicio == null) {
            $this->responderJson(400, ['ok' => false, 'error' => 'No puedes ofertar ese servicio']);
            return;
        }

        // * 2. El precio: entre la mitad y vez y media del precio de referencia
        // ? Ejemplo: referencia 10 -> puede cobrar de 5 a 15
        $referencia = $servicio['puntos_hora_referencia'];
        $minimo = round($referencia * 0.5);
        $maximo = round($referencia * 1.5);

        if ($puntos < $minimo || $puntos > $maximo) {
            $this->responderJson(400, ['ok' => false, 'error' => 'El precio tiene que estar entre ' . $minimo . ' y ' . $maximo]);
            return;
        }

        // * 3. La descripción
        if (strlen($descripcion) < 10 || strlen($descripcion) > 500) {
            $this->responderJson(400, ['ok' => false, 'error' => 'La descripción tiene que tener entre 10 y 500 caracteres']);
            return;
        }

        // * 4. Guardamos
        $idOferta = (new OfertaDao())->crear($idUsuario, $idServicio, $puntos, $descripcion);

        if ($idOferta == null) {
            $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido crear la oferta']);
            return;
        }

        $this->responderJson(201, ['ok' => true]);
    }

    private function responderJson(int $codigo, array $respuesta): void
    {
        http_response_code($codigo);
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }
}
