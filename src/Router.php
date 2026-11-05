<?php
declare(strict_types=1);

namespace App;

use App\Controllers\AuthController;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Controllers\OfertaController;

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

        // * Fragmenta la URL. Ejemplo: "/api/registro" -> ['api', 'registro']
        $segments = explode('/', trim($uri, '/'));

        $primerSegmento = $segments[0];

        switch ($primerSegmento) {
            case '':
                echo $this->twig->render('pages/inicio.html.twig', ['pagina_actual' => 'inicio']);
                break;

            // * Buscador: la página solo pone la estructura, buscador.js carga las ofertas
            case 'servicios':
                echo $this->twig->render('pages/servicios.html.twig', ['pagina_actual' => 'servicios']);
                break;

            // * GET muestra el formulario y POST lo procesa
            case 'login':
                (new AuthController($this->twig))->login();
                break;

            // * Solo muestra el formulario; lo envía registro.js a /api/registro
            case 'registro':
                (new AuthController($this->twig))->registro();
                break;

            case 'logout':
                (new AuthController($this->twig))->logout();
                break;

            // * Peticiones fetch del JavaScript: /api/...
            case 'api':
                $this->despacharApi($segments);
                break;

            default:
                $this->responderError(404, 'Página no encontrada');
                break;
        }
    }

    private function despacharApi(array $segments): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $recurso = $segments[1] ?? ''; // * "/api/registro" -> 'registro'

        switch ($recurso) {
            case 'registro':
                if ($method === 'POST') {
                    (new AuthController($this->twig))->registroApi();
                } else {
                    $this->responderError(405, 'Método no permitido');
                }
                break;

            // * Buscador de servicios: GET /api/ofertas?busqueda=...&categoria=...&orden=...
            case 'ofertas':
                if ($method === 'GET') {
                    (new OfertaController())->listarApi();
                } else {
                    $this->responderError(405, 'Método no permitido');
                }
                break;

            default:
                $this->responderError(404, 'Recurso API no encontrado');
                break;
        }
    }

    private function responderError(int $codigo, string $mensaje): void
    {
        http_response_code($codigo);
        echo $mensaje;
    }
}
