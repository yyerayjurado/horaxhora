<?php

declare(strict_types=1);

namespace App\Models;

class SolicitudTrueque extends Modelo
{
    // * Los mismos estados que el ENUM de la base de datos
    public const ESTADOS = ['SOLICITADO', 'ACEPTADO', 'RECHAZADO', 'EN_EJECUCION', 'COMPLETADO', 'CANCELADO'];

    protected int $id;
    protected int $idCliente;
    protected int $idOferta;
    protected float $horasEstimadas;
    protected int $puntosTotales;
    protected string $fechaPropuesta;
    protected string $estado;

    public function __construct(int $id, int $idCliente, int $idOferta, float $horasEstimadas, int $puntosTotales, string $fechaPropuesta, string $estado = 'SOLICITADO')
    {
        $this->id = $id;
        $this->idCliente = $idCliente;
        $this->idOferta = $idOferta;
        $this->horasEstimadas = $horasEstimadas;
        $this->puntosTotales = $puntosTotales;
        $this->fechaPropuesta = $fechaPropuesta;
        $this->estado = $estado;
    }

    public static function desdeArray(array $fila): static
    {
        return new static(
            (int)$fila['id_solicitud'],
            (int)$fila['id_cliente'],
            (int)$fila['id_oferta'],
            (float)$fila['horas_estimadas'],
            (int)$fila['puntos_totales'],
            $fila['fecha_propuesta'],
            $fila['estado']
        );
    }

    // * Solo se puede cancelar si no ha terminado ya
    public function sePuedeCancelar(): bool
    {
        return !in_array($this->estado, ['COMPLETADO', 'CANCELADO', 'RECHAZADO'], true);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }

    public function getIdOferta(): int
    {
        return $this->idOferta;
    }

    public function getHorasEstimadas(): float
    {
        return $this->horasEstimadas;
    }

    public function getPuntosTotales(): int
    {
        return $this->puntosTotales;
    }

    public function getFechaPropuesta(): string
    {
        return $this->fechaPropuesta;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }
}
