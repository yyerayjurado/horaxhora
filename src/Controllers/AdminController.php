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

// * Panel de administración
// * GET  /admin/{seccion}                -> enseña la sección
// * POST /admin/{seccion}/{accion}/{id}  -> hace la acción y vuelve a la sección
class AdminController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    // ! Si no es admin, lo mandamos al inicio
    private function comprobarAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 'ADMIN') {
            header('Location: /');
            exit();
        }
    }

    // * Guarda el mensaje que se enseña después de la redirección
    private function mensaje(string $texto, bool $esError = false): void
    {
        $_SESSION['admin_mensaje'] = ['texto' => $texto, 'error' => $esError];
    }

    // * ===== GET: pintar la sección =====
    public function panel(string $seccion): void
    {
        $this->comprobarAdmin();

        $usuarioDao = new UsuarioAdminDao();
        $pendientes = $usuarioDao->contarPendientes();

        // * /admin sin sección: si hay registros pendientes vamos a usuarios
        if ($seccion == '') {
            if ($pendientes > 0) {
                header('Location: /admin/usuarios');
            } else {
                header('Location: /admin/categorias');
            }
            exit();
        }

        // * Recogemos el mensaje de la última acción y lo borramos para que salga solo una vez
        $mensaje = $_SESSION['admin_mensaje'] ?? null;
        unset($_SESSION['admin_mensaje']);

        // * Si en la URL viene ?editar=5, el formulario se rellena con ese registro
        $editarId = (int) ($_GET['editar'] ?? 0);

        $datos = [
            'pagina_actual' => 'admin',
            'seccion' => $seccion,
            'pendientes' => $pendientes,
            'mensaje' => $mensaje,
            'editar' => null
        ];

        if ($seccion == 'categorias') {
            $categorias = (new CategoriaDao())->listar();
            $datos['categorias'] = $categorias;

            foreach ($categorias as $categoria) {
                if ($categoria['id_categoria'] == $editarId) {
                    $datos['editar'] = $categoria;
                }
            }
        } elseif ($seccion == 'servicios') {
            $filtro = (int) ($_GET['categoria'] ?? 0);

            if ($filtro > 0) {
                $servicios = (new ServicioDao())->listarTodos($filtro);
            } else {
                $servicios = (new ServicioDao())->listarTodos(null);
            }

            $datos['servicios'] = $servicios;
            $datos['categorias'] = (new CategoriaDao())->listar();
            $datos['filtro_categoria'] = $filtro;

            foreach ($servicios as $servicio) {
                if ($servicio['id_servicio'] == $editarId) {
                    $datos['editar'] = $servicio;
                }
            }
        } elseif ($seccion == 'usuarios') {
            $busqueda = trim($_GET['busqueda'] ?? '');
            $usuarios = $usuarioDao->listar($busqueda);

            $datos['usuarios'] = $usuarios;
            $datos['lista_pendientes'] = $usuarioDao->listarPendientes();
            $datos['busqueda'] = $busqueda;
            $datos['id_admin'] = $_SESSION['usuario']['id'];

            foreach ($usuarios as $usuario) {
                if ($usuario['id_usuario'] == $editarId) {
                    $datos['editar'] = $usuario;
                }
            }
        } elseif ($seccion == 'trueques') {
            $estado = $_GET['estado'] ?? '';
            $dao = new TruequeDao();

            if ($estado == '') {
                $datos['trueques'] = $dao->listar(null);
            } else {
                $datos['trueques'] = $dao->listar($estado);
            }

            $datos['resumen'] = $dao->resumen();
            $datos['filtro_estado'] = $estado;
        } elseif ($seccion == 'resenas') {
            $puntuacion = (int) ($_GET['puntuacion'] ?? 0);
            $dao = new ValoracionDao();

            if ($puntuacion >= 1 && $puntuacion <= 5) {
                $datos['resenas'] = $dao->listar($puntuacion);
            } else {
                $datos['resenas'] = $dao->listar(null);
            }

            $datos['resumen'] = $dao->resumen();
            $datos['filtro_puntuacion'] = $puntuacion;
        } else {
            // ! Sección que no existe
            header('Location: /admin');
            exit();
        }

        echo $this->twig->render('pages/admin.html.twig', $datos);
    }

    // * ===== POST: hacer una acción y volver a la sección =====
    public function accion(string $seccion, string $accion, ?int $id): void
    {
        $this->comprobarAdmin();

        // ! Todas las acciones menos "guardar" necesitan un id en la URL
        if ($id == null && $accion != 'guardar') {
            $this->mensaje('Falta el id en la URL', true);
            header('Location: /admin/' . $seccion);
            exit();
        }

        if ($seccion == 'categorias') {
            $this->accionCategorias($accion, $id);
        } elseif ($seccion == 'servicios') {
            $this->accionServicios($accion, $id);
        } elseif ($seccion == 'usuarios') {
            $this->accionUsuarios($accion, $id);
        } elseif ($seccion == 'trueques') {
            $this->accionTrueques($accion, $id);
        } elseif ($seccion == 'resenas') {
            $this->accionResenas($accion, $id);
        }

        // ! Siempre redirigimos después de un POST: así F5 no repite la acción
        header('Location: /admin/' . $seccion);
        exit();
    }

    // * ----- Categorías -----
    private function accionCategorias(string $accion, ?int $id): void
    {
        $dao = new CategoriaDao();

        if ($accion == 'borrar') {
            if ($dao->borrar($id)) {
                $this->mensaje('Categoría borrada');
            } else {
                $this->mensaje('No se ha podido borrar la categoría', true);
            }
            return;
        }

        if ($accion == 'guardar') {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if ($nombre == '') {
                $this->mensaje('El nombre es obligatorio', true);
                return;
            }

            // * Sin id = crear, con id = editar
            if ($id == null) {
                $ok = $dao->crear($nombre, $descripcion) != null;
            } else {
                $ok = $dao->actualizar($id, $nombre, $descripcion);
            }

            if ($ok) {
                $this->mensaje('Categoría guardada');
            } else {
                $this->mensaje('No se ha podido guardar (¿ya existe una con ese nombre?)', true);
            }
        }
    }

    // * ----- Servicios -----
    private function accionServicios(string $accion, ?int $id): void
    {
        $dao = new ServicioDao();

        if ($accion == 'borrar') {
            if ($dao->borrar($id)) {
                $this->mensaje('Servicio borrado');
            } else {
                $this->mensaje('No se ha podido borrar el servicio', true);
            }
            return;
        }

        if ($accion == 'guardar') {
            $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $puntos = (int) ($_POST['puntos'] ?? 0);

            if ($idCategoria == 0 || $nombre == '') {
                $this->mensaje('La categoría y el nombre son obligatorios', true);
                return;
            }
            if ($puntos < 1 || $puntos > 1000) {
                $this->mensaje('Los puntos por hora deben estar entre 1 y 1000', true);
                return;
            }

            if ($id == null) {
                $ok = $dao->crear($idCategoria, $nombre, $descripcion, $puntos) != null;
            } else {
                $ok = $dao->actualizar($id, $idCategoria, $nombre, $descripcion, $puntos);
            }

            if ($ok) {
                $this->mensaje('Servicio guardado');
            } else {
                $this->mensaje('No se ha podido guardar el servicio', true);
            }
        }
    }

    // * ----- Usuarios -----
    private function accionUsuarios(string $accion, ?int $id): void
    {
        $dao = new UsuarioAdminDao();

        if ($accion == 'guardar') {
            $this->guardarUsuario($dao, $id);
            return;
        }

        // ! El admin no puede darse de baja ni borrarse a sí mismo
        if ($id == $_SESSION['usuario']['id']) {
            $this->mensaje('No puedes hacer esto con tu propia cuenta', true);
            return;
        }

        if ($accion == 'baja') {
            $dao->cambiarActivo($id, false);
            $this->mensaje('Usuario dado de baja');
        } elseif ($accion == 'reactivar') {
            $dao->cambiarActivo($id, true);
            $this->mensaje('Usuario reactivado');
        } elseif ($accion == 'borrar') {
            if ($dao->borrar($id)) {
                $this->mensaje('Usuario borrado');
            } else {
                $this->mensaje('No se ha podido borrar el usuario', true);
            }
        } elseif ($accion == 'aprobar' || $accion == 'rechazar') {
            $this->resolverRegistro($dao, $id, $accion == 'aprobar');
        }
    }

    private function guardarUsuario(UsuarioAdminDao $dao, ?int $id): void
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $puntos = (int) ($_POST['puntos'] ?? 0);

        // * Si el teléfono viene vacío guardamos NULL
        if ($telefono == '') {
            $telefono = null;
        }

        if ($nombre == '' || $apellidos == '') {
            $this->mensaje('El nombre y los apellidos son obligatorios', true);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mensaje('El email no es válido', true);
            return;
        }
        if ($puntos < 0) {
            $this->mensaje('Los puntos no pueden ser negativos', true);
            return;
        }
        if ($dao->emailEnUso($email, $id)) {
            $this->mensaje('Ya existe otro usuario con ese email', true);
            return;
        }

        // * Crear
        if ($id == null) {
            $password = $_POST['password'] ?? '';

            if (strlen($password) < 8) {
                $this->mensaje('La contraseña debe tener al menos 8 caracteres', true);
                return;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            if ($dao->crear($nombre, $apellidos, $email, $hash, $telefono, $puntos) != null) {
                $this->mensaje('Usuario creado');
            } else {
                $this->mensaje('No se ha podido crear el usuario', true);
            }
            return;
        }

        // * Editar
        if ($dao->actualizar($id, $nombre, $apellidos, $email, $telefono, $puntos)) {
            $this->mensaje('Usuario actualizado');
        } else {
            $this->mensaje('No se ha podido guardar el usuario', true);
        }
    }

    // * Aceptar o rechazar un registro pendiente y avisar por correo
    private function resolverRegistro(UsuarioAdminDao $dao, int $id, bool $aprobar): void
    {
        // ! Cogemos el email ANTES, porque si se rechaza la cuenta se borra
        $contacto = $dao->obtenerContacto($id);

        if ($contacto == null) {
            $this->mensaje('Ese usuario no existe', true);
            return;
        }

        if ($aprobar) {
            $ok = $dao->aprobar($id);
        } else {
            $ok = $dao->rechazar($id);
        }

        if (!$ok) {
            $this->mensaje('No se ha podido completar la acción', true);
            return;
        }

        $texto = $aprobar ? 'Registro aceptado' : 'Registro rechazado';

        // ? Si falla el correo, la acción ya está hecha: solo lo avisamos en el mensaje
        try {
            $mail = new MailService(MailOptions::desdeEntorno(), new PdfService());

            if ($aprobar) {
                $mail->enviarCuentaAprobada($contacto['email'], $contacto['nombre']);
            } else {
                $mail->enviarCuentaRechazada($contacto['email'], $contacto['nombre']);
            }
        } catch (\Throwable $e) {
            $texto = $texto . ' (no se ha podido enviar el correo)';
        }

        $this->mensaje($texto);
    }

    // * ----- Trueques: solo se pueden cancelar -----
    private function accionTrueques(string $accion, ?int $id): void
    {
        if ($accion == 'cancelar') {
            if ((new TruequeDao())->cancelar($id)) {
                $this->mensaje('Trueque cancelado');
            } else {
                $this->mensaje('Este trueque ya no se puede cancelar', true);
            }
        }
    }

    // * ----- Reseñas: quitar el comentario o borrarla -----
    private function accionResenas(string $accion, ?int $id): void
    {
        $dao = new ValoracionDao();

        if ($accion == 'quitar-comentario') {
            if ($dao->quitarComentario($id)) {
                $this->mensaje('Comentario retirado');
            } else {
                $this->mensaje('No se ha podido quitar el comentario', true);
            }
        } elseif ($accion == 'borrar') {
            if ($dao->borrar($id)) {
                $this->mensaje('Reseña borrada');
            } else {
                $this->mensaje('No se ha podido borrar la reseña', true);
            }
        }
    }
}
