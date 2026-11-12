<?php // * Variables: $nombre, $email, $fecha, $puntos ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* * Márgenes del folio */
        @page { margin: 40px 50px; }

        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; font-size: 12px; line-height: 1.6; }

        .cabecera { background: #16a34a; color: #ffffff; padding: 24px; border-radius: 8px; }
        .cabecera h1 { margin: 0; font-size: 26px; }
        .cabecera p { margin: 4px 0 0; }

        h2 { color: #16a34a; font-size: 16px; margin: 28px 0 8px; border-bottom: 2px solid #dcfce7; padding-bottom: 4px; }

        .datos { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .datos td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .datos td:first-child { color: #64748b; width: 35%; }

        .puntos { font-size: 32px; font-weight: bold; color: #16a34a; }

        ol li { margin-bottom: 6px; }

        .pie { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; color: #94a3b8; font-size: 10px; }
    </style>
</head>
<body>
<div class="cabecera">
    <h1>HoraXHora</h1>
    <p>Guía de bienvenida</p>
</div>

<h2>Tu cuenta</h2>
<table class="datos">
    <tr><td>Nombre</td><td><?= htmlspecialchars($nombre) ?></td></tr>
    <tr><td>Email</td><td><?= htmlspecialchars($email) ?></td></tr>
    <tr><td>Fecha de alta</td><td><?= htmlspecialchars($fecha) ?></td></tr>
    <tr><td>Saldo inicial</td><td><span class="puntos"><?= (int) $puntos ?></span> puntos</td></tr>
</table>

<h2>Primeros pasos</h2>
<ol>
    <li><strong>Crea tus ofertas</strong> desde "Crear oferta": elige uno de tus servicios, pon tu tarifa y una descripción.</li>
    <li><strong>Busca servicios</strong> de otros profesionales y solicítalos con tus puntos.</li>
    <li><strong>Revisa tu agenda</strong> para aceptar o rechazar las solicitudes que te lleguen.</li>
    <li><strong>Confirma y valora</strong> cada trueque cuando termine: así se transfieren los puntos.</li>
</ol>

<div class="pie">HoraXHora · Banco de tiempo entre profesionales · <?= htmlspecialchars($fecha) ?></div>
</body>
</html>
