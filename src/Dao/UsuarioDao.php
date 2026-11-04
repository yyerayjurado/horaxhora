<?php
declare(strict_types=1);

namespace App\Dao;

use App\Models\Usuario;
use PDO;
use PDOException;

// ! CAMBIO (Claude): archivo renombrado de UsuarioDAO.php a UsuarioDao.php para que coincida con la clase (PSR-4)
class UsuarioDao
{
    // * Busca un usuario por su email. Si no existe (o hay error) devuelve null
    // ! CAMBIO (Claude): ahora devuelve un objeto Usuario en vez de un array
    public function buscarPorEmail(string $email): ?Usuario
    {
        try {
            $pdo = BD::getConexion();

            // ! prepare() porque el email viene del formulario
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
            $stmt->execute([$email]);

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                return null;
            }

            // ! CAMBIO (Claude): convierte la fila de la BD en un objeto Usuario
            return new Usuario(
                (int) $fila['id_usuario'],
                $fila['nombre'],
                $fila['apellidos'],
                $fila['email'],
                $fila['password'],
                $fila['telefono'],
                $fila['rol'],
                (int) $fila['puntos_saldo']
            );
        } catch (PDOException $e) {
            echo 'Error en la consulta: ' . $e->getMessage();
            return null;
        }
    }
}
