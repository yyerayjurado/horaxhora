<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Dao\ServicioDao;

class CatalogoController
{
    // * GET /api/servicios?categoria=3 → servicios de esa categoría en JSON
    public function serviciosApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $idCategoria = (int)($_GET['categoria'] ?? 0);

        // ! Si no llega una categoría válida, no consultamos nada
        if ($idCategoria <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Categoría no válida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $servicios = (new ServicioDao())->obtenerPorCategoria($idCategoria);

        echo json_encode(['ok' => true, 'servicios' => $servicios], JSON_UNESCAPED_UNICODE);
    }
}
