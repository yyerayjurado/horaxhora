<div align="center">

# 🔄 HoraXHora

**Plataforma web de trueque de servicios profesionales basada en un sistema de puntos por hora.**

Presta un servicio, gana puntos. Usa esos puntos para contratar el servicio que necesites a cualquier otro profesional de la comunidad.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-2.4-D22128?logo=apache&logoColor=white)
![Twig](https://img.shields.io/badge/Twig-3-bacf29?logo=symfony&logoColor=black)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-nativo-1572B6?logo=css3&logoColor=white)

[Qué es](#-qué-es) · [Capturas](#-capturas) · [Puesta en marcha](#-puesta-en-marcha-con-docker) · [Arquitectura](#️-arquitectura) · [Base de datos](#️-base-de-datos) · [Problemas comunes](#-problemas-comunes)

</div>

---

## 📖 Qué es

**HoraXHora** es una aplicación web **no lucrativa** de trueque de servicios. Fontaneros, electricistas, informáticos, profesores y otros profesionales intercambian servicios usando **puntos** en lugar de dinero.

El intercambio **no tiene que ser recíproco** entre las mismas dos personas:

```
Ana arregla el ordenador de Luis       →  Ana gana 20 puntos
Ana usa esos puntos con Marta (clases) →  Ana gasta 20 puntos, Marta los gana
```

Cada usuario acumula puntos prestando servicios a cualquier miembro y los canjea después con quien quiera.

> Proyecto integrador del **CFGS Desarrollo de Aplicaciones Web (DAW)**.

---

## ✨ Funcionalidades

| Módulo | Qué incluye | Estado |
|---|---|---|
| 👤 **Registro y login** | Registro en ventana modal con **50 puntos de bienvenida** · El profesional elige los servicios que sabe hacer · Login / logout con sesiones · Roles `ADMIN` y `PROFESIONAL` | ✅ |
| ✅ **Aprobación de cuentas** | Las cuentas nuevas quedan pendientes hasta que el administrador las acepta o rechaza. Mientras, no pueden iniciar sesión ni aparecen en el buscador | ✅ |
| 📧 **Correos y PDF** | Aviso al registrarse y al aprobar o rechazar la cuenta (PHPMailer + MailHog). Al aprobar se adjunta un PDF de bienvenida (Dompdf) | ✅ |
| 📚 **Catálogo de servicios** | Catálogo global gestionado **solo por el administrador** (categoría + precio de referencia en puntos/hora) | ✅ |
| 🧰 **Crear oferta** | El profesional publica uno de sus servicios con **tarifa propia** (entre el 50 % y el 150 % del precio de referencia) y una **descripción personal** | ✅ |
| 🔎 **Buscador** | Búsqueda por texto · Filtro por **categoría** · Ordenación por valoración media o por precio, sin recargar la página | ✅ |
| 🔄 **Solicitar un servicio** | Desde el buscador: horas (de 0,5 a 8), fecha y hora propuestas. Los puntos los calcula el servidor | ✅ |
| 📅 **Mi agenda** | Tres bloques: **Te toca responder**, **Próximos y en curso** e **Historial** · Cada tarjeta explica qué falta y lleva sus botones (aceptar, rechazar, cancelar, terminar, valorar) | ✅ |
| 👤 **Perfil** | Datos personales editables, saldo de puntos, valoración media y ofertas del usuario | ✅ |
| 🛡️ **Panel de administración** | CRUD de categorías y servicios · Gestión de usuarios (alta, edición, baja sin borrar el historial) · Historial de trueques con cancelación · Moderación de reseñas | ✅ |
| 🤝 **Completar el trueque** | **Doble confirmación** (cliente y prestador) y transferencia de puntos en una **transacción PDO** | ✅ |
| ⭐ **Valoraciones** | El cliente puntúa de **1 a 5 estrellas** + comentario opcional, una sola vez por trueque completado | ✅ |
| 💰 **Saldo en tiempo real** | El saldo del header se actualiza **sin recargar la página** al completar un trueque | ✅ |

---

## 📸 Capturas

| Inicio | Buscador de servicios |
|---|---|
| ![Página de inicio](docs/img/capturas/inicio.png) | ![Buscador con filtros por categoría](docs/img/capturas/buscador.png) |
| **Registro (ventana modal)** | **Solicitar un servicio** |
| ![Registro en ventana modal](docs/img/capturas/registro-modal.png) | ![Modal para solicitar un servicio](docs/img/capturas/solicitar-servicio.png) |
| **Mi agenda** | **Crear oferta** |
| ![Mi agenda por bloques](docs/img/capturas/agenda.png) | ![Crear oferta](docs/img/capturas/crear-oferta.png) |
| **Perfil** | **Panel de administración** |
| ![Perfil del profesional](docs/img/capturas/perfil.png) | ![Gestión de usuarios en el panel de administración](docs/img/capturas/admin-usuarios.png) |

<details>
<summary><b>Más capturas</b></summary>

| | |
|---|---|
| **Login** | **Inicio en móvil** |
| ![Inicio de sesión](docs/img/capturas/login.png) | ![Inicio en móvil](docs/img/capturas/inicio-movil.png) |
| **Valorar un trueque** | **Admin · Historial de trueques** |
| ![Formulario para valorar un trueque completado](docs/img/capturas/agenda-valorar.png) | ![Historial de trueques](docs/img/capturas/admin-trueques.png) |
| **Admin · Moderación de reseñas** | **Admin · Categorías** |
| ![Moderación de reseñas](docs/img/capturas/admin-resenas.png) | ![CRUD de categorías](docs/img/capturas/admin-categorias.png) |
| **Correo de cuenta aprobada (MailHog)** | **Bandeja de MailHog** |
| ![Correo de cuenta aprobada](docs/img/capturas/mailhog-correo.png) | ![Bandeja de MailHog](docs/img/capturas/mailhog-bandeja.png) |

</details>

---

## 🛠️ Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | **PHP 8.2** orientado a objetos con tipado estricto (`declare(strict_types=1)`) |
| Servidor | **Apache 2.4** en contenedor Docker |
| Base de datos | **MySQL 8.0** con acceso mediante **PDO** y sentencias preparadas |
| Plantillas | **Twig 3** |
| Correo y PDF | **PHPMailer 6.8** · **Dompdf 3** · **MailHog** para pruebas |
| Dependencias | **Composer** (autoload PSR-4: `App\` → `src/`) |
| Frontend | **JavaScript Vanilla (ES6+)**: `fetch`, `async/await`, `<template>` y manipulación del DOM |
| Maquetación | **HTML5 semántico** + **CSS3 nativo** (Flexbox, Grid y media queries). **Sin frameworks CSS** |
| Infraestructura | **Docker** + **Docker Compose** |
| Depuración | **Xdebug 3** |

**Patrones de diseño:** MVC · DAO · Singleton (conexión PDO) · arquitectura híbrida (Twig renderiza en el servidor y JavaScript carga datos con `fetch` sin recargar).

---

## 🚀 Puesta en marcha con Docker

> 📘 La guía completa, con configuración, Xdebug y solución de problemas, está en el [**manual de despliegue**](docs/manual-despliegue.md).

![Contenedores de Docker y puertos](docs/img/diagramas/despliegue.png)

### Requisitos

| Herramienta | Para qué | Comprobar |
|---|---|---|
| [Docker Desktop](https://www.docker.com/products/docker-desktop/) | Levantar Apache + PHP, MySQL y MailHog | `docker -v` |
| [Git](https://git-scm.com/) | Clonar el repositorio | `git -v` |

> No hace falta tener PHP, MySQL ni Composer instalados en tu ordenador: todo funciona dentro de Docker.

### 1. Clonar el repositorio

```bash
git clone https://github.com/yyerayjurado/proyecto-noviembre.git
cd proyecto-noviembre
```

### 2. Levantar los contenedores

```bash
docker compose up -d --build
```

La primera vez tarda unos minutos. Durante ese arranque, MySQL ejecuta automáticamente los scripts de `database/`:

1. `01_schema.sql`: crea las tablas.
2. `02_seeds.sql`: inserta los datos de prueba.

### 3. Instalar las dependencias

La carpeta `vendor/` no se sube al repositorio. El contenedor ya trae Composer:

```bash
docker compose exec app composer install
```

### 4. Abrir la aplicación

| Servicio | Dirección |
|---|---|
| 🌐 Aplicación | **http://localhost:8080** |
| 📬 Correos de prueba (MailHog) | **http://localhost:8025** |
| 🗄️ MySQL (desde fuera de Docker) | `localhost:3306` |

Para comprobar que todo está en marcha:

```bash
docker compose ps
```

Los tres servicios (`app`, `db` y `mailhog`) deben aparecer en marcha.

### 5. Usuarios de prueba

Todos tienen la contraseña **`1234`**.

| Rol | Email |
|---|---|
| Administrador | `admin@horaxhora.es` |
| Profesional | `laura@horaxhora.es` |
| Profesional | `carlos@horaxhora.es` |
| Profesional | `ana@horaxhora.es` |
| Profesional | `david@horaxhora.es` |

---

## ⚙️ Configuración

Los datos de conexión se pasan a PHP como **variables de entorno** desde `docker-compose.yml` y se leen con `getenv()`, así que no hay contraseñas escritas en el código.

| Variable | Valor | Dónde se usa |
|---|---|---|
| `DB_HOST` | `db` | `src/Dao/BD.php` |
| `DB_NAME` | `sistema_trueque` | `src/Dao/BD.php` |
| `DB_USER` | `usuario` | `src/Dao/BD.php` |
| `DB_PASS` | `usuario` | `src/Dao/BD.php` |
| `MAIL_HOST` | `mailhog` | `src/Service/MailOptions.php` |

**Conexión a MySQL con un cliente externo** (PhpStorm, MySQL Workbench, DBeaver…):

| Campo | Valor |
|---|---|
| Host | `localhost` |
| Puerto | `3306` |
| Usuario / contraseña | `usuario` / `usuario` (o `root` / `root`) |
| Base de datos | `sistema_trueque` |

---

## 🧭 Comandos útiles

| Acción | Comando |
|---|---|
| Arrancar | `docker compose up -d` |
| Arrancar reconstruyendo la imagen (tras cambiar el `Dockerfile`) | `docker compose up -d --build` |
| Parar | `docker compose down` |
| Ver logs de PHP / Apache | `docker compose logs -f app` |
| Ver logs de MySQL | `docker compose logs -f db` |
| Entrar en el contenedor de PHP | `docker compose exec app bash` |
| Abrir la consola de MySQL | `docker compose exec db mysql -u usuario -pusuario sistema_trueque` |
| **Reiniciar la base de datos desde cero** ⚠️ | `docker compose down -v && docker compose up -d` |

> ⚠️ `down -v` **borra el volumen de MySQL y todos sus datos**. Úsalo cuando cambies `01_schema.sql` o `02_seeds.sql`, porque esos scripts solo se ejecutan cuando la base de datos se crea por primera vez.

---

## 🏗️ Arquitectura

### Flujo de una petición

![Flujo de una petición en HoraXHora](docs/img/diagramas/arquitectura.png)

- **Todas las peticiones entran por `public/index.php`**. El `.htaccess` manda a `index.php` cualquier URL que no sea un archivo real (CSS, JS, imágenes).
- El **Router** parte la URL en segmentos y decide con un `switch` qué método del **Controller** ejecutar.
- El **Controller** pide los datos al **Dao**, que es la única capa que habla con la base de datos.
- Los **Service** se encargan del envío de correos y de la generación de PDF.
- El Controller responde de una de dos formas:
  - **HTML** renderizado con **Twig**, para las páginas y el panel de administración (formularios POST).
  - **JSON**, para las peticiones `fetch` del buscador, crear oferta, solicitudes y agenda.

### Estructura de carpetas

```
proyecto-noviembre/
├── Dockerfile                  # PHP 8.2 + Apache + pdo_mysql + Xdebug + Composer
├── docker-compose.yml          # Servicios: app, db y mailhog
├── apache.conf                 # DocumentRoot en public/ y AllowOverride All
├── xdebug.ini                  # Configuración de depuración
├── composer.json               # Twig, PHPMailer y Dompdf
│
├── database/
│   ├── 01_schema.sql           # Creación de tablas
│   └── 02_seeds.sql            # Datos de prueba
│
├── docs/
│   ├── manual-despliegue.md
│   ├── diario-ia.md
│   └── img/                    # capturas/ y diagramas/ (E/R, clases, arquitectura...)
│
├── public/                     # 🔓 Única carpeta accesible desde el navegador
│   ├── index.php               # Punto de entrada
│   ├── .htaccess               # Rutas amigables hacia index.php
│   └── assets/
│       ├── css/                # estilos.css: todo el CSS en un solo archivo, por secciones
│       ├── js/modules/         # buscador, registro, modal, crear_oferta, agenda...
│       └── img/
│
├── src/                        # 🔒 Código PHP (namespace App\)
│   ├── Router.php              # Enrutado de URLs a controladores
│   ├── Controllers/            # Auth, Perfil, Catalogo, Oferta, Solicitud, Agenda, Admin
│   ├── Dao/                    # BD.php (Singleton) + un Dao por tabla
│   ├── Models/                 # Categoria, Servicio, OfertaProfesional, SolicitudTrueque, Valoracion, Usuarios
│   └── Service/                # MailService, PdfService, MailOptions, CreateMailer
│
└── templates/                  # 🔒 Plantillas
    ├── layout/                 # base.html.twig
    ├── partials/               # header, footer, formulario y modal de registro
    ├── pages/                  # inicio, login, registro, servicios, perfil, crearoferta, agenda, admin
    ├── emails/                 # Plantillas de los correos
    └── pdf/                    # Plantilla del PDF de bienvenida
```

### Diagrama de clases MVC

Capas de la aplicación: el `Router` reparte las peticiones a los **Controllers**, que usan los **Dao** (todos comparten la conexión de `BD`, patrón Singleton) y los **Service** de correo y PDF.

![Diagrama de clases MVC: Router, Controllers, Dao y Service](docs/img/diagramas/clases-mvc.png)

### Diagrama de clases de los modelos

![Diagrama de clases de los modelos](docs/img/diagramas/clases-modelos.png)

---

## 🗄️ Base de datos

### Diagrama Entidad-Relación

![Diagrama Entidad-Relación de HoraXHora](docs/img/diagramas/er.png)

**Restricciones clave:**
- `OFERTA_PROFESIONAL`: `UNIQUE (id_profesional, id_servicio)`. Un profesional no puede ofrecer el mismo servicio dos veces.
- `SERVICIO_PROFESIONAL` (tabla añadida al modelo del enunciado): guarda los servicios que el profesional eligió al registrarse. Solo puede crear ofertas de esos servicios.
- `VALORACION.id_solicitud` es `UNIQUE`, así que cada trueque se valora **una sola vez**.
- `CHECK`: el saldo no puede ser negativo, las horas, las tarifas y los puntos tienen que ser mayores que 0 y la puntuación va de 1 a 5.
- La solicitud no copia los datos de la oferta: apunta a `id_oferta`. Solo se guarda `puntos_totales`, para conservar el precio acordado aunque el profesional cambie su tarifa más adelante.
- `activo` y `aprobado` en `USUARIO`: dar de baja a un usuario lo desactiva en vez de borrarlo, porque el `ON DELETE CASCADE` borraría todo su historial de trueques.

---

## 🔄 Ciclo de vida de un trueque

![Ciclo de vida de un trueque](docs/img/diagramas/ciclo-trueque.png)

**Seguridad en las acciones:** cada `UPDATE` de la agenda comprueba en el `WHERE` el estado actual **y** que el trueque pertenece a quien hace la acción. Así nadie puede aceptar o cancelar un trueque ajeno cambiando el id de la URL.

**Doble confirmación y puntos:** cuando uno de los dos marca el servicio como terminado, el trueque pasa a `EN_EJECUCION`. Cuando lo marca el otro, pasa a `COMPLETADO` y se descuentan los puntos al cliente y se suman al prestador **dentro de una misma transacción PDO** (`beginTransaction` → `commit`, o `rollBack` si algo falla), para que nunca se pierdan ni se dupliquen puntos. Si el cliente no tiene saldo suficiente, el `CHECK (puntos_saldo >= 0)` de la tabla hace fallar la transacción y no se mueve nada.

**Valoración:** con el trueque completado, el cliente puede valorarlo una vez. El `INSERT ... SELECT` solo inserta si el trueque es suyo y está `COMPLETADO`, y el `UNIQUE` de `id_solicitud` impide valorar dos veces.

---

## 🐞 Depuración con Xdebug

El contenedor incluye **Xdebug 3** (puerto `9003`, `client_host=host.docker.internal`).

**PhpStorm:** *Settings → PHP → Servers*, añade `localhost` en el puerto `8080`, activa **Use path mappings** y relaciona la carpeta del proyecto con `/var/www/html`. Después pulsa **Start Listening for PHP Debug Connections**.

**VS Code:** instala la extensión **PHP Debug** y crea `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Xdebug (Docker)",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
      }
    }
  ]
}
```

---

## 🧯 Problemas comunes

<details>
<summary><b>❌ <code>port is already allocated</code> (puerto 8080 o 3306 ocupado)</b></summary>

Hay otro programa usando ese puerto, por ejemplo un MySQL o XAMPP instalado en tu ordenador. Cambia el puerto de la izquierda en `docker-compose.yml`:

```yaml
ports:
  - "3307:3306"   # en vez de "3306:3306"
```
</details>

<details>
<summary><b>❌ <code>Failed opening required '.../vendor/autoload.php'</code></b></summary>

Faltan las dependencias. Ejecuta el [paso 3](#3-instalar-las-dependencias).
</details>

<details>
<summary><b>❌ <code>Class "App\..." not found</code></b></summary>

El nombre del archivo, de la carpeta o del `namespace` no coincide exactamente con la clase. En Mac funciona aunque no coincidan las mayúsculas, pero en Docker (Linux) no: `UsuarioDao.php` tiene que contener `class UsuarioDao`, y la carpeta es `Service`, no `service`. Después:

```bash
docker compose exec app composer dump-autoload
```
</details>

<details>
<summary><b>❌ He cambiado el <code>.sql</code> y la base de datos no cambia</b></summary>

Los scripts de `database/` solo se ejecutan la **primera vez** que se crea la base de datos. Reiníciala (⚠️ borra los datos):

```bash
docker compose down -v
docker compose up -d
```
</details>

<details>
<summary><b>❌ <code>No se ha podido conectar con la base de datos</code> justo al arrancar</b></summary>

MySQL tarda unos segundos en estar listo la primera vez. Espera un poco y recarga. Puedes seguir el arranque con `docker compose logs -f db` hasta que aparezca `ready for connections`.
</details>

<details>
<summary><b>❌ Error 403 o todas las rutas dan 404 excepto la portada</b></summary>

Apache no está usando `apache.conf` (`DocumentRoot` en `public/` y `AllowOverride All`) o la imagen está desactualizada. Reconstrúyela:

```bash
docker compose up -d --build
```
</details>

<details>
<summary><b>❌ No veo el banner en los correos de MailHog</b></summary>

MailHog no muestra en su pestaña HTML las imágenes incrustadas con `cid`. El correo es correcto: compruébalo en la pestaña **Source**.
</details>

---

## 📄 Documentación

| Documento | Contenido |
|---|---|
| [`docs/manual-despliegue.md`](docs/manual-despliegue.md) | Manual de despliegue del entorno Docker |
| [`docs/diario-ia.md`](docs/diario-ia.md) | Diario de uso de IA (prompt, respuesta de la IA y conclusión de cada consulta) |
| Diagrama E/R | [Sección Base de datos](#️-base-de-datos) |
| Diagrama de clases MVC | [Sección Arquitectura](#diagrama-de-clases-mvc) · [`docs/img/diagramas/clases-mvc.png`](docs/img/diagramas/clases-mvc.png) |

---

## 👤 Autor

**Yeray Jurado Vela**
CFGS Desarrollo de Aplicaciones Web · IES Las Fuentezuelas (Jaén)

<div align="center">

<sub>Proyecto académico sin ánimo de lucro.</sub>

</div>
