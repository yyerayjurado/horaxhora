<?php // * Variables: $nombre, $cidBanner, $urlApp ?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Cuenta activa</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 0;">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;">
            <tr><td><img src="cid:<?= $cidBanner ?>" alt="HoraXHora" width="600" style="display:block;width:100%;height:auto;"></td></tr>
            <tr><td style="padding:32px;">
                <h1 style="margin:0 0 16px;font-size:22px;">¡Bienvenido, <?= htmlspecialchars($nombre) ?>! 🎉</h1>
                <p style="margin:0 0 12px;line-height:1.6;">Tu cuenta ya está <strong style="color:#16a34a;">activa</strong>. Empiezas con <strong>50 puntos</strong> de bienvenida.</p>
                <p style="margin:0 0 24px;line-height:1.6;">Te adjuntamos un PDF con los primeros pasos para sacarle partido.</p>
                <table cellpadding="0" cellspacing="0"><tr><td style="background:#16a34a;border-radius:999px;">
                    <a href="<?= htmlspecialchars($urlApp) ?>/login" style="display:inline-block;padding:14px 28px;color:#ffffff;font-weight:bold;text-decoration:none;">Iniciar sesión</a>
                </td></tr></table>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
