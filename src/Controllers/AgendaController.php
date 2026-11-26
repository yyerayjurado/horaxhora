<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\TruequeDao;
use Twig\Environment;

// * Mi agenda
// * GET /agenda                    -> la página (vacía, la rellena agenda.js)
// * GET /api/agenda                -> los trueques del usuario en JSON
// * POST /api/agenda/{accion}/{id} -> aceptar, rechazar o cancelar un trueque
class AgendaController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    // * La página: solo pone la estructura, los datos los pide agenda.js
    public function panel(): void
    {
        // ! Sin sesión no hay agenda: lo mandamos al login
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit();
        }

        echo $this->twig->render('pages/agenda.html.twig', ['pagina_actual' => 'agenda']);
    }

    // * GET /api/agenda: devuelve las solicitudes recibidas y las enviadas
    public function listarApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // ! Sin sesión, error 401 (no autorizado)
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Tienes que iniciar sesión']);
            return;
        }

        // ! El usuario SIEMPRE sale de la sesión, nunca de la URL
        $idUsuario = $_SESSION['usuario']['id'];
        $dao = new TruequeDao();

        echo json_encode([
            'ok' => true,
            'recibidas' => $dao->recibidas($idUsuario),
            'enviadas' => $dao->enviadas($idUsuario)
        ], JSON_UNESCAPED_UNICODE);
    }

    // * POST /api/agenda/{accion}/{id}: aceptar, rechazar o cancelar un trueque
    public function accionApi(string $accion, int $idSolicitud): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Tienes que iniciar sesión'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // ! El usuario SIEMPRE sale de la sesión: el DAO comprueba que el trueque es suyo
        $idUsuario = $_SESSION['usuario']['id'];
        $dao = new TruequeDao();

        if ($accion == 'aceptar') {
            $ok = $dao->aceptar($idSolicitud, $idUsuario);
            $texto = 'Solicitud aceptada';
        } elseif ($accion == 'rechazar') {
            $ok = $dao->rechazar($idSolicitud, $idUsuario);
            $texto = 'Solicitud rechazada';
        } elseif ($accion == 'cancelar') {
            $ok = $dao->cancelarDeCliente($idSolicitud, $idUsuario);
            $texto = 'Solicitud cancelada';
        } else {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Acción no válida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // * Si no ha cambiado nada: no es suyo, no existe o ya no está en el estado que toca
        if (!$ok) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'No se puede hacer esto con este trueque'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode(['ok' => true, 'mensaje' => $texto], JSON_UNESCAPED_UNICODE);
    }
}
