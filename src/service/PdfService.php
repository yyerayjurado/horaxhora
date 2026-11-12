<?php
declare(strict_types=1);

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private string $carpetaPlantillas;

    public function __construct()
    {
        $this->carpetaPlantillas = __DIR__ . '/../../templates/pdf';
    }

    // * Convierte una plantilla PHP en un texto HTML
    public function renderTemplate(string $templatePath, array $data): string
    {
        // * extract convierte ['nombre' => 'Ana'] en la variable $nombre dentro de la plantilla
        // ! EXTR_SKIP: si $data trae una clave "templatePath", no pisa nuestra variable
        extract($data, EXTR_SKIP);

        // * ob_start = "output buffer": los echo de la plantilla se guardan aquí en vez de salir por pantalla
        ob_start();
        include $templatePath;

        // * Devuelve todo lo guardado y vacía el buffer
        return ob_get_clean();
    }

    // * Genera un PDF a partir de una plantilla y devuelve su contenido (para adjuntarlo o descargarlo)
    public function generatePdfFromTemplate(
        string $plantilla,
        array $datos,
        string $papel = 'A4',
        string $orientacion = 'portrait'
    ): string {
        $html = $this->renderTemplate($this->carpetaPlantillas . '/' . $plantilla, $datos);

        $opciones = new Options();
        // ! DejaVu Sans viene con dompdf y tiene tildes y ñ; las fuentes por defecto no
        $opciones->set('defaultFont', 'DejaVu Sans');
        // ! No dejamos que el PDF cargue cosas de Internet (más seguro y más rápido)
        $opciones->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($papel, $orientacion);
        $dompdf->render();

        return $dompdf->output();
    }
}
