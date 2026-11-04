<?php
declare(strict_types=1);

namespace App;

use App\Controllers\AuthController;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Router
{
    private Environment $twig;

    public function __construct()
    {
        session_start();

        // * Configuración de Twig
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        $this->twig = new Environment($loader);

        // * Variables disponibles en todas las plantillas
        $this->twig->addGlobal('app_nombre', 'HoraXHora');
        $this->twig->addGlobal('usuario', $_SESSION['usuario'] ?? null);
    }

    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // * Fragmenta la URL. Ejemplo: "/login" -> ['login']
        $segments = explode('/', trim($uri, '/'));

        $primerSegmento = $segments[0];

        switch ($primerSegmento) {
            case '':
                echo $this->twig->render('pages/inicio.html.twig', ['pagina_actual' => 'inicio']);
                break;

            case 'servicios':
                echo $this->twig->render('pages/inicio.html.twig', ['pagina_actual' => 'servicios']);
                break;

            // * GET muestra el formulario y POST lo procesa
            case 'login':
                (new AuthController($this->twig))->login();
                break;

            case 'logout':
                (new AuthController($this->twig))->logout();
                break;

            default:
                $this->responderError(404, 'Página no encontrada');
                break;
        }
    }

    private function responderError(int $codigo, string $mensaje): void
    {
        http_response_code($codigo);
        echo $mensaje;
    }
}
