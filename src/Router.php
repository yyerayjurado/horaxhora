<?php
declare(strict_types=1);

namespace App;

use App\Controllers\AdminController;
use App\Controllers\AgendaController;
use App\Controllers\AuthController;
use App\Controllers\CatalogoController;
use App\Controllers\OfertaController;
use App\Controllers\PerfilController;
use App\Controllers\SolicitudController;
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

            // ! Panel de administración: GET /admin/{seccion} pinta, POST /admin/{seccion}/{accion}/{id} actúa
            case 'admin':
                $this->despacharAdmin($segments);
                break;

            // * /ofertas/crear: página para crear ofertas (crear-oferta.js carga los datos)
            case 'ofertas':
                if (($segments[1] ?? '') === 'crear') {
                    (new OfertaController($this->twig))->paginaCrear();
                } else {
                    $this->responderError(404, 'Página no encontrada');
                }
                break;

            // * Mi agenda: la página solo pone la estructura, agenda.js carga los trueques
            case 'agenda':
                (new AgendaController($this->twig))->panel();
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

            // * GET /api/ofertas?busqueda=... -> buscador de servicios
            // * POST /api/ofertas -> crear una oferta nueva (desde "Crear oferta")
            case 'ofertas':
                if ($method === 'GET') {
                    (new OfertaController($this->twig))->listarApi();
                } elseif ($method === 'POST') {
                    (new OfertaController($this->twig))->crearApi();
                } else {
                    $this->responderError(405, 'Método no permitido');
                }
                break;

            // * Crear oferta: GET /api/mis-ofertas devuelve lo que puedo ofertar y lo que ya oferto
            case 'mis-ofertas':
                if ($method === 'GET') {
                    (new OfertaController($this->twig))->misOfertasApi();
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

            // * Mi agenda: GET /api/agenda devuelve mis trueques en JSON
            // * POST /api/agenda/aceptar/7 (o rechazar, cancelar) cambia el estado de un trueque
            case 'agenda':
                if ($method === 'GET') {
                    (new AgendaController($this->twig))->listarApi();
                } elseif ($method === 'POST') {
                    $accion = $segments[2] ?? '';
                    $id = (int) ($segments[3] ?? 0);
                    (new AgendaController($this->twig))->accionApi($accion, $id);
                } else {
                    $this->responderError(405, 'Método no permitido');
                }
                break;

            // * Solicitar un servicio: POST /api/solicitudes (lo llama buscador.js)
            case 'solicitudes':
                if ($method === 'POST') {
                    (new SolicitudController())->crearApi();
                } else {
                    $this->responderError(405, 'Método no permitido');
                }
                break;

            default:
                $this->responderError(404, 'Recurso API no encontrado');
                break;
        }
    }

    // * "/admin/usuarios/aprobar/7" -> sección 'usuarios', acción 'aprobar', id 7
    private function despacharAdmin(array $segments): void
    {
        $seccion = $segments[1] ?? '';
        $accion = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : null;
        $controller = new AdminController($this->twig);

        // ! Las acciones que cambian datos solo por POST (un enlace GET no puede borrar nada)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->accion($seccion, $accion, $id);
        } else {
            $controller->panel($seccion);
        }
    }

    private function responderError(int $codigo, string $mensaje): void
    {
        http_response_code($codigo);
        echo $mensaje;
    }
}
