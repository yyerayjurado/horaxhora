<?php
declare(strict_types=1);

namespace App\Models;

class Categoria
{
    protected int $id;
    protected string $nombre;
    protected string $descripcion;

    public function __construct(int $id, string $nombre, string $descripcion = '')
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }

    public static function desdeArray(array $fila): static
    {
        return new static(
            (int) $fila['id_categoria'],
            $fila['nombre'],
            $fila['descripcion'] ?? ''
        );
    }

    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): string { return $this->descripcion; }
}
