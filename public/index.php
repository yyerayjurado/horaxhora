<?php
    declare(strict_types=1);

    // * Autoload de Composer (carga Twig y clases de src/)
    require_once __DIR__ . '/../vendor/autoload.php';

    use App\Controllers\AuthController;
    use App\Dao\BD;
    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    session_start();

    // * Configuración de Twig
    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Environment($loader);

    // * Variables disponibles en todas las plantillas
    $twig->addGlobal('app_nombre', 'HoraXHora');
    $twig->addGlobal('usuario', $_SESSION['usuario'] ?? null);

    // * Router mínimo. Luego cada caso llamará a su controlador
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    switch ($ruta) {
        case '/':
            echo $twig->render('pages/inicio.html.twig', ['pagina_actual' => 'inicio']);
            break;

        case '/servicios':
            echo $twig->render('pages/inicio.html.twig', ['pagina_actual' => 'servicios']);
            break;

        // * Botón "Entrar" del header → muestra el login (GET) y lo procesa (POST)
        case '/login':
            (new AuthController($twig))->login();
            break;

        // * Botón "Salir" del header
        case '/logout':
            (new AuthController($twig))->logout();
            break;

        default:
            http_response_code(404);
            echo '404 - Página no encontrada';
    }
