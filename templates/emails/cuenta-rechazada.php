<?php // * Variables: $nombre, $cidBanner, $urlApp ?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Solicitud no aceptada</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 0;">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;">
            <tr><td><img src="cid:<?= $cidBanner ?>" alt="HoraXHora" width="600" style="display:block;width:100%;height:auto;"></td></tr>
            <tr><td style="padding:32px;">
                <h1 style="margin:0 0 16px;font-size:22px;">Hola, <?= htmlspecialchars($nombre) ?></h1>
                <p style="margin:0 0 12px;line-height:1.6;">Hemos revisado tu solicitud y, por ahora, no hemos podido aceptarla.</p>
                <p style="margin:0 0 12px;line-height:1.6;">Puedes volver a registrarte con el mismo email cuando quieras.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
