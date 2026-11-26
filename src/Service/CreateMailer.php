<?php
declare(strict_types=1);

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;

// * Crea un PHPMailer ya configurado a partir de un MailOptions.
// * Así la configuración SMTP está en un solo sitio y MailService no la repite
class CreateMailer
{
    public static function crear(MailOptions $opciones): PHPMailer
    {
        // ! true = si algo falla, lanza una excepción (en vez de devolver false sin avisar)
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $opciones->host;
        $mail->Port = $opciones->port;

        // * Solo se autentica si hay usuario (MailHog no lo necesita)
        $mail->SMTPAuth = $opciones->username !== '';
        if ($mail->SMTPAuth) {
            $mail->Username = $opciones->username;
            $mail->Password = $opciones->password;
        }

        if ($opciones->encryption !== '') {
            $mail->SMTPSecure = $opciones->encryption;
        } else {
            // ! MailHog no tiene TLS: si PHPMailer intenta activarlo solo, falla la conexión
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        // * Para que las tildes y la ñ salgan bien
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        $mail->setFrom($opciones->fromEmail, $opciones->fromName);

        return $mail;
    }
}
