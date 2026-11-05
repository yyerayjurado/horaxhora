<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Dao\OfertaDao;

class OfertaController
{
    // * GET /api/ofertas?busqueda=...&categoria=...&orden=...
    // * Lo llama buscador.js y devuelve las ofertas en JSON
    public function listarApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // * Los filtros llegan en la URL, por eso se leen de $_GET
        $texto = trim($_GET['busqueda'] ?? '');
        $categoria = $_GET['categoria'] ?? '';
        $orden = $_GET['orden'] ?? 'valoracion';

        // * Categoría vacía = todas. Si viene, se pasa a número
        $idCategoria = $categoria !== '' ? (int) $categoria : null;

        $ofertas = (new OfertaDao())->buscar($texto, $idCategoria, $orden);

        echo json_encode(['ok' => true, 'ofertas' => $ofertas], JSON_UNESCAPED_UNICODE);
    }
}
