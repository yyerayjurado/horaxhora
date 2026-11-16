<?php

declare(strict_types=1);

namespace App\Models;

// * Servicio del catálogo oficial (solo lo gestiona el admin)
class Servicio extends Modelo
{
    protected int $id;
    protected int $idCategoria;
    protected string $nombre;
    protected string $descripcion;
    protected int $puntosHoraReferencia;

    public function __construct(int $id, int $idCategoria, string $nombre, string $descripcion, int $puntosHoraReferencia)
    {
        $this->id = $id;
        $this->idCategoria = $idCategoria;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->puntosHoraReferencia = $puntosHoraReferencia;
    }

    public static function desdeArray(array $fila): static
    {
        return new static(
            (int)$fila['id_servicio'],
            (int)$fila['id_categoria'],
            $fila['nombre'],
            $fila['descripcion'] ?? '',
            (int)$fila['puntos_hora_referencia']
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdCategoria(): int
    {
        return $this->idCategoria;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getPuntosHoraReferencia(): int
    {
        return $this->puntosHoraReferencia;
    }
}
