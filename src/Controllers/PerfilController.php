<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\PerfilDAO;
use Twig\Environment;

class PerfilController
{
    private PerfilDAO $dao;

    public function __construct(private Environment $twig)
    {
        $this->dao = new PerfilDAO();
    }

    // * GET /perfil: muestra los datos, las ofertas y la valoración del usuario
    public function mostrar(string $mensaje = '', string $error = ''): void
    {
        // ! Sin sesión no hay perfil
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit();
        }

        $idUsuario = (int) $_SESSION['usuario']['id'];
        $valoracion = $this->dao->obtenerValoracion($idUsuario);

        echo $this->twig->render('pages/perfil.html.twig', [
            'pagina_actual'      => 'perfil',
            'usuario'            => $this->dao->obtenerUsuario($idUsuario),
            'ofertas'            => $this->dao->obtenerOfertas($idUsuario),
            'servicios'          => $this->dao->obtenerServiciosDisponibles($idUsuario),
            'media_valoracion'   => (float) ($valoracion['media'] ?? 0),
            'total_valoraciones' => (int) ($valoracion['total'] ?? 0),
            'mensaje'            => $mensaje,
            'error'              => $error,
        ]);
    }

    // * POST /perfil/actualizar: guarda los datos personales
    public function actualizar(): void
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit();
        }

        $idUsuario = (int) $_SESSION['usuario']['id'];
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');

        if ($nombre === '' || $apellidos === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mostrar('', 'Revisa los datos: nombre, apellidos y un email válido son obligatorios');
            return;
        }

        if ($this->dao->actualizarUsuario($idUsuario, $nombre, $apellidos, $email, $telefono)) {
            // * Refrescamos la sesión para que el header muestre el nombre nuevo
            $_SESSION['usuario']['nombre'] = $nombre;
            $this->mostrar('Datos actualizados correctamente');
        } else {
            $this->mostrar('', 'No se han podido guardar los cambios (¿email ya en uso?)');
        }
    }
}
