<?php
declare(strict_types=1);

// * Autoload de Composer (carga Twig y clases de src/)
require __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

session_start();

// * Configuración de Twig
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, [
    'cache' => false, // ! Pon una ruta de caché en producción
    'debug' => true,
]);

// TODO: quitar esto cuando tengas el login hecho. Sirve para probar el header con sesión
if (isset($_GET['demo'])) {
    $_SESSION['usuario'] = [
        'nombre'       => 'Yeray',
        'rol'          => $_GET['demo'] === 'admin' ? 'ADMIN' : 'PROFESIONAL',
        'puntos_saldo' => 50,
    ];
}
if (isset($_GET['salir'])) {
    unset($_SESSION['usuario']);
}

// * Nombre de la app disponible en todas las plantillas
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

    default:
        http_response_code(404);
        echo '404 - Página no encontrada';
}