<?php
declare(strict_types=1);

namespace App;

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CatalogoController;
use App\Controllers\OfertaController;
use App\Controllers\PerfilController;
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

            // * /registro → página completa
            // * /registro/formulario → solo el formulario (lo pide el modal)
            case 'registro':
                if (($segments[1] ?? '') === 'formulario') {
                    (new AuthController($this->twig))->registroFormulario();
                } else {
                    (new AuthController($this->twig))->registro();
                }
                break;

            case 'logout':
                (new AuthController($this->twig))->logout();
                break;

            // * GET /perfil muestra el perfil, POST /perfil/actualizar guarda los datos
            case 'perfil':
                $this->despacharPerfil($segments);
                break;

            // ! Panel de administración: solo la página, los datos van por /api/admin/...
            case 'admin':
                (new AdminController($this->twig))->panel();
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

    private function despacharPerfil(array $segments): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $accion = $segments[1] ?? ''; // * "/perfil/actualizar" -> 'actualizar'
        $controller = new PerfilController($this->twig);

        switch ($accion) {
            case '':
                $controller->mostrar();
                break;

            case 'actualizar':
                if ($method === 'POST') {
                    $controller->actualizar();
                } else {
                    $this->responderError(405, 'Método no permitido');
                }
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

            // * Servicios de una categoría para el registro. GET /api/servicios?categoria=3
            case 'servicios':
                if ($method === 'GET') {
                    (new CatalogoController())->serviciosApi();
                } else {
                    $this->responderError(405, 'Método no permitido');
                }
                break;

            // * Panel de administración: /api/admin/...
            case 'admin':
                $this->despacharAdmin($segments);
                break;

            default:
                $this->responderError(404, 'Recurso API no encontrado');
                break;
        }
    }

    // * "/api/admin/categorias/3" -> recurso 'categorias', id 3
    private function despacharAdmin(array $segments): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $recurso = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : null;
        $controller = new AdminController($this->twig);

        switch ($recurso) {
            case 'categorias':
                $controller->categoriasApi($method, $id);
                break;

            case 'servicios':
                $controller->serviciosApi($method, $id);
                break;

            case 'usuarios':
                $controller->usuariosApi($method, $id);
                break;

            case 'trueques':
                $controller->truequesApi($method, $id);
                break;

            // ! Nuevo: moderación de reseñas
            case 'resenas':
                $controller->resenasApi($method, $id);
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
