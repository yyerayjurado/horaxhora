<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\ServicioDao;
use App\Dao\UsuarioAdminDao;
use App\Dao\UsuarioDao;
use App\Service\MailOptions;
use App\Service\MailService;
use App\Service\PdfService;
use Twig\Environment;

class AuthController
{
    public function __construct(private Environment $twig)
    {
    }

    // * Muestra el login (GET) y lo procesa (POST)
    public function login(): void
    {
        // * Si ya está logueado, lo mandamos al inicio
        if (isset($_SESSION['usuario'])) {
            header('Location: /');
            exit();
        }

        $error = null;

        // * Procesar el formulario cuando se envía por POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // * Buscamos el usuario en la BD
            $usuario = (new UsuarioDao())->buscarPorEmail($email);
            $usuarioAdminDao = new UsuarioAdminDao();

            // ! La contraseña está guardada con hash, por eso password_verify y no ===
            if (!$usuario || !password_verify($password, $usuario->getPassword())) {
                $error = 'Email o contraseña incorrectos.';
            } elseif (!$usuarioAdminDao->estaAprobado($usuario->getId())) {
                // ! Se ha registrado pero el admin todavía no le ha aceptado
                $error = 'Tu cuenta está pendiente de aprobación. Te avisaremos por correo cuando el administrador la revise.';
            } elseif (!$usuarioAdminDao->estaActivo($usuario->getId())) {
                // ! El admin le ha dado de baja: no le dejamos entrar
                $error = 'Tu cuenta está dada de baja. Contacta con el administrador.';
            } else {
                // * Guardar datos en la sesión (nunca la contraseña)
                $_SESSION['usuario'] = [
                    'id'           => $usuario->getId(),
                    'nombre'       => $usuario->getNombre(),
                    'rol'          => $usuario->getRol(),
                    'puntos_saldo' => $usuario->getPuntosSaldo(),
                ];

                header('Location: /');
                exit();
            }
        }

        echo $this->twig->render('pages/login.html.twig', [
            'pagina_actual' => 'login',
            'error'         => $error,
        ]);
    }

    // * GET /registro: página de registro de respaldo (por si falla el modal)
    public function registro(): void
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: /');
            exit();
        }

        echo $this->twig->render('pages/registro.html.twig', [
            'pagina_actual' => 'registro',
            'categorias'    => (new ServicioDao())->obtenerCategorias(),
        ]);
    }

    // * GET /registro/formulario: devuelve solo el formulario para meterlo en el modal
    public function registroFormulario(): void
    {
        // ! Si ya está logueado no tiene sentido darle el formulario
        if (isset($_SESSION['usuario'])) {
            http_response_code(403);
            echo '<p>Ya has iniciado sesión.</p>';
            return;
        }

        echo $this->twig->render('partials/registro-modal.html.twig', [
            'categorias' => (new ServicioDao())->obtenerCategorias(),
        ]);
    }

    // * POST /api/registro: recibe JSON (desde el modal o la página) y responde JSON
    public function registroApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // * Los datos llegan en el cuerpo de la petición como JSON, no en $_POST
        $datos = json_decode(file_get_contents('php://input'), true) ?? [];

        $nombre    = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $email     = trim($datos['email'] ?? '');
        $telefono  = trim($datos['telefono'] ?? '');
        $password  = $datos['password'] ?? '';
        $password2 = $datos['password2'] ?? '';
        $servicios = $datos['servicios'] ?? [];

        // ! Validamos también aquí: el JS se lo puede saltar cualquiera
        if ($nombre === '' || $apellidos === '') {
            $this->responderJson(400, ['ok' => false, 'error' => 'El nombre y los apellidos son obligatorios']);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderJson(400, ['ok' => false, 'error' => 'El email no es válido']);
            return;
        }
        if (strlen($password) < 8) {
            $this->responderJson(400, ['ok' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres']);
            return;
        }
        if ($password !== $password2) {
            $this->responderJson(400, ['ok' => false, 'error' => 'Las contraseñas no coinciden']);
            return;
        }

        // ! Los servicios: tiene que elegir al menos uno y todos tienen que existir en el catálogo
        if (!is_array($servicios) || count($servicios) === 0) {
            $this->responderJson(400, ['ok' => false, 'error' => 'Elige al menos un servicio que sepas hacer']);
            return;
        }

        $servicioDao = new ServicioDao();
        $idsServicios = [];

        foreach ($servicios as $idServicio) {
            $idServicio = (int) $idServicio;

            if (!$servicioDao->existe($idServicio)) {
                $this->responderJson(400, ['ok' => false, 'error' => 'Has elegido un servicio que no existe']);
                return;
            }

            // * Evitamos duplicados (la tabla tiene clave profesional + servicio)
            if (!in_array($idServicio, $idsServicios)) {
                $idsServicios[] = $idServicio;
            }
        }

        $usuarioDao = new UsuarioDao();

        // * Comprobamos que el email no esté ya registrado
        if ($usuarioDao->buscarPorEmail($email)) {
            $this->responderJson(409, ['ok' => false, 'error' => 'Ya existe una cuenta con ese email']);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        // * El teléfono es opcional: si viene vacío se guarda NULL
        $id = $usuarioDao->insertar($nombre, $apellidos, $email, $hash, $telefono !== '' ? $telefono : null);

        if ($id === null) {
            $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido crear la cuenta. Inténtalo de nuevo.']);
            return;
        }

        // ! NO se crean ofertas: solo guardamos QUÉ servicios puede ofertar.
        // ! Las ofertas (con su tarifa y descripción) las crea él después desde "Crear oferta"
        $servicioDao->guardarDeProfesional($id, $idsServicios);

        // ! La cuenta queda pendiente: NO iniciamos sesión hasta que el admin la acepte
        (new UsuarioAdminDao())->marcarPendiente($id);

        // * Correo de "hemos recibido tu solicitud". Si falla, el registro ya está hecho igualmente
        try {
            $mailService = new MailService(MailOptions::desdeEntorno(), new PdfService());
            $mailService->enviarSolicitudRecibida($email, $nombre);
        } catch (\Throwable $e) {
            error_log('Correo no enviado: ' . $e->getMessage());
        }

        $this->responderJson(201, ['ok' => true, 'pendiente' => true]);
    }

    // * Cierra la sesión y vuelve al login
    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit();
    }

    // * Envía una respuesta en JSON con su código HTTP
    private function responderJson(int $codigo, array $respuesta): void
    {
        http_response_code($codigo);
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }
}
