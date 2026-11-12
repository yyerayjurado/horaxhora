<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\CategoriaDao;
use App\Dao\ServicioDao;
use App\Dao\TruequeDao;
use App\Dao\UsuarioAdminDao;
use App\Dao\ValoracionDao;
use App\Service\MailOptions;
use App\Service\MailService;
use App\Service\PdfService;
use Twig\Environment;

class AdminController
{
    // * Estados que existen en el ENUM de solicitudes_trueque
    private const ESTADOS_TRUEQUE = ['SOLICITADO', 'ACEPTADO', 'RECHAZADO', 'EN_EJECUCION', 'COMPLETADO', 'CANCELADO'];

    public function __construct(private Environment $twig)
    {
    }

    // ! Todo el panel pasa por aquí: solo entra el rol ADMIN
    private function esAdmin(): bool
    {
        return isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'ADMIN';
    }

    // * GET /admin: la página del panel (los datos los carga admin.js)
    public function panel(): void
    {
        if (!$this->esAdmin()) {
            header('Location: /');
            exit();
        }

        echo $this->twig->render('pages/admin.html.twig', [
            'pagina_actual' => 'admin',
            // * Para el contador rojo de la pestaña Usuarios
            'pendientes'    => (new UsuarioAdminDao())->contarPendientes(),
        ]);
    }

    // * ===== Categorías: /api/admin/categorias y /api/admin/categorias/{id} =====
    public function categoriasApi(string $method, ?int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->esAdmin()) {
            $this->responderJson(403, ['ok' => false, 'error' => 'No tienes permiso']);
            return;
        }

        $dao = new CategoriaDao();

        // * GET: listar
        if ($method === 'GET') {
            $this->responderJson(200, ['ok' => true, 'categorias' => $dao->listar()]);
            return;
        }

        // * DELETE: borrar
        if ($method === 'DELETE') {
            if ($id === null) {
                $this->responderJson(400, ['ok' => false, 'error' => 'Falta el id de la categoría']);
                return;
            }

            if ($dao->borrar($id)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido borrar la categoría']);
            }
            return;
        }

        // * POST y PUT llevan datos: los leemos y validamos una sola vez
        $datos = $this->leerJson();
        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $this->responderJson(400, ['ok' => false, 'error' => 'El nombre es obligatorio (máximo 100 caracteres)']);
            return;
        }

        // * POST: crear
        if ($method === 'POST') {
            $nuevoId = $dao->crear($nombre, $descripcion);

            if ($nuevoId === null) {
                // ? El fallo más típico es el UNIQUE del nombre
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido crear la categoría (¿ya existe una con ese nombre?)']);
            } else {
                $this->responderJson(201, ['ok' => true, 'id' => $nuevoId]);
            }
            return;
        }

        // * PUT: editar
        if ($method === 'PUT') {
            if ($id === null) {
                $this->responderJson(400, ['ok' => false, 'error' => 'Falta el id de la categoría']);
                return;
            }

            if ($dao->actualizar($id, $nombre, $descripcion)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido guardar la categoría (¿ya existe una con ese nombre?)']);
            }
            return;
        }

        $this->responderJson(405, ['ok' => false, 'error' => 'Método no permitido']);
    }

    // * ===== Servicios: /api/admin/servicios y /api/admin/servicios/{id} =====
    public function serviciosApi(string $method, ?int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->esAdmin()) {
            $this->responderJson(403, ['ok' => false, 'error' => 'No tienes permiso']);
            return;
        }

        $dao = new ServicioDao();

        // * GET: listar (con filtro opcional ?categoria=3)
        if ($method === 'GET') {
            $idCategoria = null;
            if (isset($_GET['categoria']) && $_GET['categoria'] !== '') {
                $idCategoria = (int) $_GET['categoria'];
            }

            $this->responderJson(200, ['ok' => true, 'servicios' => $dao->listarTodos($idCategoria)]);
            return;
        }

        // * DELETE: borrar
        if ($method === 'DELETE') {
            if ($id === null) {
                $this->responderJson(400, ['ok' => false, 'error' => 'Falta el id del servicio']);
                return;
            }

            if ($dao->borrar($id)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido borrar el servicio']);
            }
            return;
        }

        // * POST y PUT llevan datos: los leemos y validamos una sola vez
        $datos = $this->leerJson();
        $idCategoria = (int) ($datos['id_categoria'] ?? 0);
        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $puntos = (int) ($datos['puntos'] ?? 0);

        if ($idCategoria <= 0) {
            $this->responderJson(400, ['ok' => false, 'error' => 'Elige una categoría']);
            return;
        }
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            $this->responderJson(400, ['ok' => false, 'error' => 'El nombre es obligatorio (máximo 150 caracteres)']);
            return;
        }
        // ? He puesto 1000 como máximo; cámbialo si el profe da otro límite
        if ($puntos < 1 || $puntos > 1000) {
            $this->responderJson(400, ['ok' => false, 'error' => 'Los puntos por hora deben estar entre 1 y 1000']);
            return;
        }

        // * POST: crear
        if ($method === 'POST') {
            $nuevoId = $dao->crear($idCategoria, $nombre, $descripcion, $puntos);

            if ($nuevoId === null) {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido crear el servicio']);
            } else {
                $this->responderJson(201, ['ok' => true, 'id' => $nuevoId]);
            }
            return;
        }

        // * PUT: editar
        if ($method === 'PUT') {
            if ($id === null) {
                $this->responderJson(400, ['ok' => false, 'error' => 'Falta el id del servicio']);
                return;
            }

            if ($dao->actualizar($id, $idCategoria, $nombre, $descripcion, $puntos)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido guardar el servicio']);
            }
            return;
        }

        $this->responderJson(405, ['ok' => false, 'error' => 'Método no permitido']);
    }

    // * ===== Usuarios: /api/admin/usuarios y /api/admin/usuarios/{id} =====
    public function usuariosApi(string $method, ?int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->esAdmin()) {
            $this->responderJson(403, ['ok' => false, 'error' => 'No tienes permiso']);
            return;
        }

        $dao = new UsuarioAdminDao();
        $idAdmin = (int) $_SESSION['usuario']['id'];

        // * GET: usuarios aprobados (con buscador opcional ?busqueda=texto) + registros pendientes
        // * Mandamos también el id del admin para que el JS no le ponga botones de baja/borrar a él mismo
        if ($method === 'GET') {
            $busqueda = trim($_GET['busqueda'] ?? '');
            $this->responderJson(200, [
                'ok'         => true,
                'usuarios'   => $dao->listar($busqueda),
                'pendientes' => $dao->listarPendientes(),
                'id_admin'   => $idAdmin,
            ]);
            return;
        }

        // * Todo lo que no sea crear necesita saber de qué usuario hablamos
        if ($method !== 'POST' && $id === null) {
            $this->responderJson(400, ['ok' => false, 'error' => 'Falta el id del usuario']);
            return;
        }

        // ! El admin no puede darse de baja ni borrarse a sí mismo (se quedaría fuera del panel)
        if (($method === 'DELETE' || $method === 'PATCH') && $id === $idAdmin) {
            $this->responderJson(400, ['ok' => false, 'error' => 'No puedes hacer esto con tu propia cuenta']);
            return;
        }

        // * DELETE: borrar del todo
        if ($method === 'DELETE') {
            if ($dao->borrar($id)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido borrar el usuario']);
            }
            return;
        }

        $datos = $this->leerJson();

        // * PATCH con {aprobado: true/false}: aceptar o rechazar un registro pendiente
        if ($method === 'PATCH' && array_key_exists('aprobado', $datos)) {
            // ! Guardamos nombre y email ANTES: si se rechaza, la cuenta se borra y ya no los tendríamos
            $contacto = $dao->obtenerContacto($id);
            if ($contacto === null) {
                $this->responderJson(404, ['ok' => false, 'error' => 'Ese usuario no existe']);
                return;
            }

            $aprobar = $datos['aprobado'] === true;
            $hecho = $aprobar ? $dao->aprobar($id) : $dao->rechazar($id);

            if (!$hecho) {
                $this->responderJson(400, ['ok' => false, 'error' => 'No se ha podido completar (¿ya estaba aprobado?)']);
                return;
            }

            // * Avisamos por correo. Si el correo falla, la acción YA está hecha: solo lo apuntamos
            $aviso = null;
            try {
                $mailService = new MailService(MailOptions::desdeEntorno(), new PdfService());

                if ($aprobar) {
                    $mailService->enviarCuentaAprobada($contacto['email'], $contacto['nombre']);
                } else {
                    $mailService->enviarCuentaRechazada($contacto['email'], $contacto['nombre']);
                }
            } catch (\Throwable $e) {
                error_log('Correo no enviado: ' . $e->getMessage());
                $aviso = 'Hecho, pero no se ha podido enviar el correo de aviso';
            }

            $this->responderJson(200, ['ok' => true, 'aviso' => $aviso]);
            return;
        }

        // * PATCH con {activo: true/false}: dar de baja o reactivar
        if ($method === 'PATCH') {
            $activo = (bool) ($datos['activo'] ?? true);

            if ($dao->cambiarActivo($id, $activo)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido cambiar el estado']);
            }
            return;
        }

        // * POST y PUT: datos del usuario
        $nombre    = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $email     = trim($datos['email'] ?? '');
        $telefono  = trim($datos['telefono'] ?? '');
        $telefono  = $telefono !== '' ? $telefono : null; // * Vacío = NULL en la BD
        $puntos    = (int) ($datos['puntos'] ?? 0);

        if ($nombre === '' || $apellidos === '') {
            $this->responderJson(400, ['ok' => false, 'error' => 'El nombre y los apellidos son obligatorios']);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderJson(400, ['ok' => false, 'error' => 'El email no es válido']);
            return;
        }
        if ($puntos < 0) {
            $this->responderJson(400, ['ok' => false, 'error' => 'Los puntos no pueden ser negativos']);
            return;
        }

        // * Al editar no contamos el email del propio usuario
        $excluirId = $method === 'PUT' ? $id : null;
        if ($dao->emailEnUso($email, $excluirId)) {
            $this->responderJson(409, ['ok' => false, 'error' => 'Ya existe otro usuario con ese email']);
            return;
        }

        // * POST: crear (la contraseña solo se pide al crear)
        if ($method === 'POST') {
            $password = $datos['password'] ?? '';

            if (strlen($password) < 8) {
                $this->responderJson(400, ['ok' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres']);
                return;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $nuevoId = $dao->crear($nombre, $apellidos, $email, $hash, $telefono);

            if ($nuevoId === null) {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido crear el usuario']);
            } else {
                $this->responderJson(201, ['ok' => true, 'id' => $nuevoId]);
            }
            return;
        }

        // * PUT: editar
        if ($method === 'PUT') {
            if ($dao->actualizar($id, $nombre, $apellidos, $email, $telefono, $puntos)) {
                // * Si el admin se edita a sí mismo, refrescamos su sesión (nombre y puntos del header)
                if ($id === $idAdmin) {
                    $_SESSION['usuario']['nombre'] = $nombre;
                    $_SESSION['usuario']['puntos_saldo'] = $puntos;
                }
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido guardar el usuario']);
            }
            return;
        }

        $this->responderJson(405, ['ok' => false, 'error' => 'Método no permitido']);
    }

    // * ===== Trueques: /api/admin/trueques y /api/admin/trueques/{id} =====
    public function truequesApi(string $method, ?int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->esAdmin()) {
            $this->responderJson(403, ['ok' => false, 'error' => 'No tienes permiso']);
            return;
        }

        $dao = new TruequeDao();

        // * GET: historial (con filtro opcional ?estado=COMPLETADO) + resumen
        if ($method === 'GET') {
            $estado = $_GET['estado'] ?? '';

            // ! Solo aceptamos estados que existen; cualquier otra cosa es un error
            if ($estado !== '' && !in_array($estado, self::ESTADOS_TRUEQUE, true)) {
                $this->responderJson(400, ['ok' => false, 'error' => 'Estado no válido']);
                return;
            }

            $this->responderJson(200, [
                'ok'       => true,
                'trueques' => $dao->listar($estado !== '' ? $estado : null),
                'resumen'  => $dao->resumen(),
            ]);
            return;
        }

        // * PATCH: cancelar un trueque
        if ($method === 'PATCH') {
            if ($id === null) {
                $this->responderJson(400, ['ok' => false, 'error' => 'Falta el id del trueque']);
                return;
            }

            if ($dao->cancelar($id)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(400, ['ok' => false, 'error' => 'Solo se pueden cancelar trueques que aún no han terminado']);
            }
            return;
        }

        $this->responderJson(405, ['ok' => false, 'error' => 'Método no permitido']);
    }

    // * ===== Reseñas: /api/admin/resenas y /api/admin/resenas/{id} =====
    public function resenasApi(string $method, ?int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->esAdmin()) {
            $this->responderJson(403, ['ok' => false, 'error' => 'No tienes permiso']);
            return;
        }

        $dao = new ValoracionDao();

        // * GET: listar (con filtro opcional ?puntuacion=1..5) + resumen
        if ($method === 'GET') {
            $puntuacion = null;

            if (isset($_GET['puntuacion']) && $_GET['puntuacion'] !== '') {
                $puntuacion = (int) $_GET['puntuacion'];

                // ! Solo de 1 a 5 estrellas
                if ($puntuacion < 1 || $puntuacion > 5) {
                    $this->responderJson(400, ['ok' => false, 'error' => 'La puntuación debe estar entre 1 y 5']);
                    return;
                }
            }

            $this->responderJson(200, [
                'ok'      => true,
                'resenas' => $dao->listar($puntuacion),
                'resumen' => $dao->resumen(),
            ]);
            return;
        }

        // * El resto de acciones necesitan saber de qué reseña hablamos
        if ($id === null) {
            $this->responderJson(400, ['ok' => false, 'error' => 'Falta el id de la reseña']);
            return;
        }

        // * PATCH: quitar solo el comentario (las estrellas se quedan)
        if ($method === 'PATCH') {
            if ($dao->quitarComentario($id)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(400, ['ok' => false, 'error' => 'Esta reseña no tiene comentario o no existe']);
            }
            return;
        }

        // * DELETE: borrar la reseña entera
        if ($method === 'DELETE') {
            if ($dao->borrar($id)) {
                $this->responderJson(200, ['ok' => true]);
            } else {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido borrar la reseña']);
            }
            return;
        }

        $this->responderJson(405, ['ok' => false, 'error' => 'Método no permitido']);
    }

    // * Los datos de fetch llegan como JSON en el cuerpo de la petición
    private function leerJson(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function responderJson(int $codigo, array $respuesta): void
    {
        http_response_code($codigo);
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }
}
