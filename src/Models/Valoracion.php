<?php

declare(strict_types=1);

namespace App\Models;

// * Reseña que deja el cliente cuando el trueque se completa (1 por solicitud)
class Valoracion extends Modelo
{
    protected int $id;
    protected int $idSolicitud;
    protected int $puntuacion;
    protected ?string $comentario;

    public function __construct(int $id, int $idSolicitud, int $puntuacion, ?string $comentario = null)
    {
        $this->id = $id;
        $this->idSolicitud = $idSolicitud;
        $this->puntuacion = $puntuacion;
        $this->comentario = $comentario;
    }

    public static function desdeArray(array $fila): static
    {
        return new static(
            (int)$fila['id_valoracion'],
            (int)$fila['id_solicitud'],
            (int)$fila['puntuacion'],
            $fila['comentario'] ?? null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdSolicitud(): int
    {
        return $this->idSolicitud;
    }

    public function getPuntuacion(): int
    {
        return $this->puntuacion;
    }

    public function getComentario(): ?string
    {
        return $this->comentario;
    }
}
