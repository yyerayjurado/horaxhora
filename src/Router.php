<?php
    // * Autoload de Composer (carga Twig y clases de src/)
    require_once __DIR__ . '/../vendor/autoload.php';

    use App\Controllers\AuthController;
    use App\Router;
    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    session_start();

    // * Configuración de Twig
    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Environment($loader, [
        'cache' => false, // ! Pon una ruta de caché en producción
        'debug' => true,
    ]);

    // * Variables disponibles en todas las plantillas
    $twig->addGlobal('app_nombre', 'HoraXHora');
    $twig->addGlobal('usuario', $_SESSION['usuario'] ?? null);

    $router = new Router();

    // * Páginas
    $router->get('/', function () use ($twig) {
        echo $twig->render('pages/inicio.html.twig', ['pagina_actual' => 'inicio']);
    });

    $router->get('/servicios', function () use ($twig) {
        echo $twig->render('pages/inicio.html.twig', ['pagina_actual' => 'servicios']);
    });

    // * Login: GET muestra el formulario y POST lo procesa (los dos van al mismo método)
    $router->get('/login', function () use ($twig) {
        (new AuthController($twig))->login();
    });

    $router->post('/login', function () use ($twig) {
        (new AuthController($twig))->login();
    });

    $router->get('/logout', function () use ($twig) {
        (new AuthController($twig))->logout();
    });

    // * Busca la ruta pedida y la ejecuta
    $router->ejecutar();
