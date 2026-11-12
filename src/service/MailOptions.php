<?php
declare(strict_types=1);

namespace App\Service;

// * Configuración del servidor de correo en un objeto, en vez de pasar un array $smtpConfig suelto
class MailOptions
{
    public function __construct(
        public string $host,
        public int $port,
        public string $fromEmail,
        public string $fromName,
        public string $username = '',
        public string $password = '',
        public string $encryption = '' // ? '' = sin cifrar (MailHog). 'tls' o 'ssl' para un servidor real
    ) {
    }

    // * Lee la configuración de las variables de entorno (docker-compose.yml)
    // ! Si no existen (XAMPP del profe), usa MailHog en localhost:1025
    public static function desdeEntorno(): self
    {
        return new self(
            getenv('MAIL_HOST') ?: 'localhost',
            (int) (getenv('MAIL_PORT') ?: 1025),
            getenv('MAIL_FROM') ?: 'no-reply@horaxhora.es',
            'HoraXHora'
        );
    }
}
