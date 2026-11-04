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

    // * Cierra la sesión y vuelve al login
    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit();
    }
}
