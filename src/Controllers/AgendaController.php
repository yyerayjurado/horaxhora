<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\TruequeDao;
use App\Dao\UsuarioDao;
use App\Dao\ValoracionDao;
use Twig\Environment;

// * Mi agenda
// * GET /agenda                    -> la página (vacía, la rellena agenda.js)
// * GET /api/agenda                -> los trueques del usuario en JSON
// * POST /api/agenda/{accion}/{id} -> aceptar, rechazar, cancelar, confirmar o valorar un trueque
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
        } elseif ($accion == 'confirmar') {
            // * Marcar el servicio como terminado (doble confirmación)
            $this->confirmar($dao, $idSolicitud, $idUsuario);
            return;
        } elseif ($accion == 'valorar') {
            // * Dejar la valoración de un trueque completado
            $this->valorar($idSolicitud, $idUsuario);
            return;
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

    // * Doble confirmación: cada uno marca que el servicio está hecho
    // * - Si el otro aún no ha confirmado: se guarda la mía y queda EN_EJECUCION
    // * - Si el otro ya había confirmado: se completa y se pasan los puntos
    private function confirmar(TruequeDao $dao, int $idSolicitud, int $idUsuario): void
    {
        $trueque = $dao->buscarParaConfirmar($idSolicitud);

        // ! No existe o no está ACEPTADO / EN_EJECUCION
        if ($trueque === null) {
            $this->responderError('No se puede confirmar este trueque');
            return;
        }

        // * ¿Soy el cliente o el prestador? Si no soy ninguno, no es mi trueque
        if ($idUsuario == $trueque['id_cliente']) {
            $quien = 'cliente';
            $yoConfirmado = $trueque['confirmado_cliente'];
            $otroConfirmado = $trueque['confirmado_prestador'];
        } elseif ($idUsuario == $trueque['id_profesional']) {
            $quien = 'prestador';
            $yoConfirmado = $trueque['confirmado_prestador'];
            $otroConfirmado = $trueque['confirmado_cliente'];
        } else {
            $this->responderError('No se puede confirmar este trueque');
            return;
        }

        // ! No se puede confirmar dos veces
        if ($yoConfirmado) {
            $this->responderError('Ya habías confirmado este trueque');
            return;
        }

        if (!$otroConfirmado) {
            // * Soy el primero: solo guardo mi confirmación
            $dao->marcarConfirmado($idSolicitud, $quien);
            $texto = 'Hecho. Falta que la otra persona confirme que el servicio está terminado';
        } else {
            // * Soy el segundo: se completa y se pasan los puntos
            $ok = $dao->completar(
                $idSolicitud,
                (int) $trueque['id_cliente'],
                (int) $trueque['id_profesional'],
                (int) $trueque['puntos_totales']
            );

            if (!$ok) {
                $this->responderError('No se ha podido completar: el cliente no tiene puntos suficientes');
                return;
            }
            $texto = 'Trueque completado: los puntos ya se han transferido';
        }

        // * Saldo nuevo en la sesión (el header lo usa) y en la respuesta (para cambiarlo sin recargar)
        $saldo = (new UsuarioDao())->obtenerSaldo($idUsuario);
        $_SESSION['usuario']['puntos_saldo'] = $saldo;

        echo json_encode(['ok' => true, 'mensaje' => $texto, 'saldo' => $saldo], JSON_UNESCAPED_UNICODE);
    }

    // * El cliente valora el trueque: puntuación de 1 a 5 y comentario opcional
    private function valorar(int $idSolicitud, int $idUsuario): void
    {
        $datos = json_decode(file_get_contents('php://input'), true) ?? [];

        $puntuacion = (int) ($datos['puntuacion'] ?? 0);
        $comentario = trim($datos['comentario'] ?? '');

        // * Validaciones (aunque el formulario ya las haga, el JS se puede saltar)
        if ($puntuacion < 1 || $puntuacion > 5) {
            $this->responderError('La puntuación tiene que estar entre 1 y 5');
            return;
        }

        // ? mb_strlen cuenta letras, no bytes (strlen contaría la ñ o las tildes como 2)
        if (mb_strlen($comentario) > 500) {
            $this->responderError('El comentario no puede tener más de 500 caracteres');
            return;
        }

        // * Si no escribe nada, guardamos NULL en vez de un texto vacío
        if ($comentario == '') {
            $comentario = null;
        }

        // ! El cliente sale de la sesión: el DAO comprueba que el trueque es suyo y está COMPLETADO
        $ok = (new ValoracionDao())->crear($idSolicitud, $idUsuario, $puntuacion, $comentario);

        if (!$ok) {
            $this->responderError('No puedes valorar este trueque (o ya lo has valorado)');
            return;
        }

        echo json_encode(['ok' => true, 'mensaje' => '¡Gracias! Tu valoración se ha guardado'], JSON_UNESCAPED_UNICODE);
    }

    // * Respuesta de error en JSON
    private function responderError(string $mensaje): void
    {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => $mensaje], JSON_UNESCAPED_UNICODE);
    }
}
