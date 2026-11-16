<?php
declare(strict_types=1);

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    // * Id con el que la plantilla encuentra el banner: <img src="cid:banner">
    private const CID_BANNER = 'banner';

    private string $carpetaPlantillas;
    private string $rutaBanner;
    private string $urlApp;

    // * Recibe la configuración y el PdfService para poder crear PDFs él mismo
    public function __construct(private MailOptions $opciones, private PdfService $pdfService)
    {
        $this->carpetaPlantillas = __DIR__ . '/../../templates/emails';
        $this->rutaBanner = __DIR__ . '/../../public/assets/img/banner-correo.png';
        $this->urlApp = getenv('APP_URL') ?: 'http://localhost:8080';
    }

    // * Convierte una plantilla PHP en un texto HTML
    public function renderTemplate(string $templatePath, array $data): string
    {
        // ! Si la plantilla no existe, paramos aquí con un error claro (si no, se envía un correo roto)
        if (!is_file($templatePath)) {
            throw new \RuntimeException('No existe la plantilla: ' . $templatePath);
        }

        // ! EXTR_SKIP: si $data trae una clave "templatePath", no pisa nuestra variable
        extract($data, EXTR_SKIP);

        // * Los echo de la plantilla se guardan en el buffer en vez de salir por pantalla
        ob_start();
        include $templatePath;

        return ob_get_clean();
    }

    // * Envío genérico: todos los correos de la aplicación pasan por aquí
    // * $pdfs: ['nombre-archivo.pdf' => contenido del PDF]
    // ? Al llevar imagen incrustada (cid), el correo va como multipart/related.
    // ? MailHog no sabe pintarlo en su pestaña HTML: hay que mirarlo en "Source" o "MIME"
    public function enviar(string $para, string $nombre, string $asunto, string $plantilla, array $datos, array $pdfs = []): void
    {
        $mail = CreateMailer::crear($this->opciones);

        $mail->addAddress($para, $nombre);
        $mail->Subject = $asunto;
        $mail->isHTML(true);

        // ! El banner se embebe ANTES de renderizar la plantilla, que lo usa con src="cid:banner"
        $mail->addEmbeddedImage($this->rutaBanner, self::CID_BANNER, 'banner.png');

        // ! Estas dos variables las usan TODAS las plantillas de correo. Sin ellas: "Undefined variable"
        $datos['cidBanner'] = self::CID_BANNER;
        $datos['urlApp'] = $this->urlApp;

        $html = $this->renderTemplate($this->carpetaPlantillas . '/' . $plantilla, $datos);
        $mail->Body = $html;

        // * Versión solo texto, para clientes de correo que no muestran HTML
        $mail->AltBody = trim(html_entity_decode(strip_tags($html)));

        foreach ($pdfs as $nombreArchivo => $contenido) {
            $mail->addStringAttachment($contenido, $nombreArchivo, PHPMailer::ENCODING_BASE64, 'application/pdf');
        }

        // ! Si falla, PHPMailer lanza una Exception (la recoge quien llama)
        $mail->send();
    }

    // * ===== Correos concretos de HoraXHora =====

    // * Al registrarse: "hemos recibido tu solicitud"
    public function enviarSolicitudRecibida(string $email, string $nombre): void
    {
        $this->enviar(
            $email,
            $nombre,
            'Hemos recibido tu solicitud en HoraXHora',
            'solicitud-recibida.php',
            ['nombre' => $nombre]
        );
    }

    // * El admin acepta la cuenta: correo + PDF de bienvenida adjunto
    public function enviarCuentaAprobada(string $email, string $nombre): void
    {
        $pdf = $this->pdfService->generatePdfFromTemplate('bienvenida.php', [
            'nombre' => $nombre,
            'email'  => $email,
            'fecha'  => date('d/m/Y'),
            'puntos' => 50,
        ]);

        $this->enviar(
            $email,
            $nombre,
            '¡Ya tienes acceso a HoraxHora, bienvenido!',
            'cuenta-aprobada.php',
            ['nombre' => $nombre],
            ['bienvenida-horaxhora.pdf' => $pdf]
        );
    }

    // * El admin rechaza la cuenta
    public function enviarCuentaRechazada(string $email, string $nombre): void
    {
        $this->enviar(
            $email,
            $nombre,
            'Sobre tu solicitud en HoraXHora',
            'cuenta-rechazada.php',
            ['nombre' => $nombre]
        );
    }
}
