<?php
declare(strict_types=1);

namespace App\Models;

/**
 * * Representa una fila de la tabla "usuarios" como objeto.
 */
class Usuario
{
    private int $id;
    private string $nombre;
    private string $apellidos;
    private string $email;
    private string $password; // ! Es el hash, nunca la contraseña en claro
    private ?string $telefono;
    private string $rol;
    private int $puntosSaldo;

    public function __construct(
        int $id,
        string $nombre,
        string $apellidos,
        string $email,
        string $password,
        ?string $telefono,
        string $rol,
        int $puntosSaldo
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->email = $email;
        $this->password = $password;
        $this->telefono = $telefono;
        $this->rol = $rol;
        $this->puntosSaldo = $puntosSaldo;
    }

    // * Getters
    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellidos(): string { return $this->apellidos; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getTelefono(): ?string { return $this->telefono; }
    public function getRol(): string { return $this->rol; }
    public function getPuntosSaldo(): int { return $this->puntosSaldo; }
}
