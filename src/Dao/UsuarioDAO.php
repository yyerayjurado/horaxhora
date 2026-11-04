<?php
declare(strict_types=1);

namespace App\Dao;

use PDO;
use PDOException;

class UsuarioDao
{
    // * Busca un usuario por su email. Si no existe (o hay error) devuelve null
    public function buscarPorEmail(string $email): ?array
    {
        try {
            $pdo = BD::getConexion();

            // ! prepare() porque el email viene del formulario
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
            $stmt->execute([$email]);

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila ?: null;
        } catch (PDOException $e) {
            echo 'Error en la consulta: ' . $e->getMessage();
            return null;
        }
    }
}
