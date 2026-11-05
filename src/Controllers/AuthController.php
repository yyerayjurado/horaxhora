<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\UsuarioDao;
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

            // ! La contraseña está guardada con hash, por eso password_verify y no ===
            // ! CAMBIO (Claude): $usuario ahora es un objeto Usuario, por eso se usan getters en vez de ['...']
            if ($usuario && password_verify($password, $usuario->getPassword())) {
                // * Guardar datos en la sesión (nunca la contraseña)
                // * Sigue siendo un array porque Twig lo lee directamente (usuario.nombre)
                $_SESSION['usuario'] = [
                    'id'           => $usuario->getId(),
                    'nombre'       => $usuario->getNombre(),
                    'rol'          => $usuario->getRol(),
                    'puntos_saldo' => $usuario->getPuntosSaldo(),
                ];

                header('Location: /');
                exit();
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        }

        echo $this->twig->render('pages/login.html.twig', [
            'pagina_actual' => 'login',
            'error'         => $error,
        ]);
    }

    // * GET /registro: solo muestra el formulario (lo envía registro.js con fetch)
    public function registro(): void
    {
        // * Si ya está logueado, lo mandamos al inicio
        if (isset($_SESSION['usuario'])) {
            header('Location: /');
            exit();
        }

        echo $this->twig->render('pages/registro.html.twig', ['pagina_actual' => 'registro']);
    }

    // * POST /api/registro: recibe JSON desde registro.js y responde JSON
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

        $usuarioDao = new UsuarioDao();

        // ! Se vuelve a validar todo: lo del navegador se puede saltar
        if ($nombre === '' || $apellidos === '' || $email === '' || $password === '') {
            $this->responderJson(422, ['ok' => false, 'error' => 'Rellena todos los campos obligatorios.']);
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderJson(422, ['ok' => false, 'error' => 'El email no tiene un formato válido.']);
        } elseif (strlen($password) < 8) {
            $this->responderJson(422, ['ok' => false, 'error' => 'La contraseña tiene que tener al menos 8 caracteres.']);
        } elseif ($password !== $password2) {
            $this->responderJson(422, ['ok' => false, 'error' => 'Las contraseñas no coinciden.']);
        } elseif ($usuarioDao->buscarPorEmail($email) !== null) {
            $this->responderJson(409, ['ok' => false, 'error' => 'Ya existe una cuenta con ese email.']);
        } else {
            // ! Nunca se guarda la contraseña tal cual, solo su hash
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // * El teléfono es opcional: si viene vacío se guarda NULL
            $id = $usuarioDao->insertar($nombre, $apellidos, $email, $hash, $telefono !== '' ? $telefono : null);

            if ($id === null) {
                $this->responderJson(500, ['ok' => false, 'error' => 'No se ha podido crear la cuenta. Inténtalo de nuevo.']);
            } else {
                // * Cuenta creada: lo dejamos ya logueado con sus 50 puntos de bienvenida
                $_SESSION['usuario'] = [
                    'id'           => $id,
                    'nombre'       => $nombre,
                    'rol'          => 'PROFESIONAL',
                    'puntos_saldo' => 50,
                ];

                $this->responderJson(201, ['ok' => true]);
            }
        }
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
