<?php

declare(strict_types=1);

namespace App\Models;

// * Servicio del catálogo que ofrece un profesional con su propia tarifa
class OfertaProfesional extends Modelo
{
    protected int $id;
    protected int $idProfesional;
    protected int $idServicio;
    protected int $puntosHora;
    protected string $descripcionPersonal;
    protected bool $activo;

    public function __construct(int $id, int $idProfesional, int $idServicio, int $puntosHora, string $descripcionPersonal, bool $activo = true)
    {
        $this->id = $id;
        $this->idProfesional = $idProfesional;
        $this->idServicio = $idServicio;
        $this->puntosHora = $puntosHora;
        $this->descripcionPersonal = $descripcionPersonal;
        $this->activo = $activo;
    }

    public static function desdeArray(array $fila): static
    {
        return new static(
            (int)$fila['id_oferta'],
            (int)$fila['id_profesional'],
            (int)$fila['id_servicio'],
            (int)$fila['puntos_hora'],
            $fila['descripcion_personal'] ?? '',
            (bool)$fila['activo']
        );
    }

    // * Lo que cuesta un trueque de X horas con esta oferta
    public function calcularPuntos(float $horas): int
    {
        return (int)round($this->puntosHora * $horas);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdProfesional(): int
    {
        return $this->idProfesional;
    }

    public function getIdServicio(): int
    {
        return $this->idServicio;
    }

    public function getPuntosHora(): int
    {
        return $this->puntosHora;
    }

    public function getDescripcionPersonal(): string
    {
        return $this->descripcionPersonal;
    }

    public function estaActiva(): bool
    {
        return $this->activo;
    }
}
