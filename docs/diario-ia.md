# Diario de uso de IA

Registro de las consultas hechas a la IA durante el desarrollo del proyecto: qué se pidió, qué se obtuvo, por qué se pidió y qué se ha aprendido.

---

## 1. Revisión del diagrama E/R y propuesta de estructura de carpetas
**Fecha:** 29/10/2026

### Prompt
> Te adjunto el diagrama Entidad-Relación de mi proyecto (trueque de servicios). Revísalo y dime qué errores de diseño tiene (nombres de entidades, claves, tipos de datos, relaciones y datos duplicados). Después, a partir del modelo corregido, propón una estructura de carpetas para una aplicación PHP con MVC + DAO, plantillas Twig, JavaScript con fetch y despliegue en Docker, explicando brevemente para qué sirve cada carpeta.

### Resultado
- Lista de correcciones al diagrama: `catalogo` pasa a ser `categoria`; en `oferta servicio` sobran los campos copiados de `servicio` y falta el `UNIQUE (id_profesional, id_servicio)`; el trueque debe apuntar a `id_oferta` en lugar de copiar datos; `valoraciones` debe enlazar con `id_solicitud` (relación 1:1); y hay tipos mal elegidos (`estado` como ENUM, confirmaciones como BOOLEAN, `telefono` como VARCHAR, `puntuacion` como TINYINT 1–5).
- Propuesta de estructura de carpetas con `public/` como única carpeta accesible, capas Models / DAO / Controllers, vistas Twig y módulos JS.

### Motivo
Antes de empezar a programar quería asegurarme de que la base de datos era correcta, porque de cada tabla sale un Modelo y un DAO. Si el diseño está mal, todo lo demás estará mal.

### Qué he aprendido


---

## 2. Adaptar la estructura a la plantilla del profesor
**Fecha:** 29/10/2026

### Prompt
> Te adjunto un .zip con la estructura de carpetas que nos ha dado el profesor y que tenemos que seguir obligatoriamente. Adapta tu propuesta anterior a esa plantilla respetando sus nombres (`Dao`, `Views` dentro de `src`, `App.php`, `Autoloader.php`, `Router.php`), añade solo lo que falte para cumplir el enunciado y genérame el esqueleto con los archivos vacíos para descargarlo.

### Resultado
- Se comprobó que los archivos de la plantilla venían vacíos.
- Se generó el esqueleto `proyecto-noviembre.zip` respetando la plantilla y añadiendo `database/` (SQL de esquema y datos de prueba), `docs/`, `public/assets/` (CSS y módulos JS) y `composer.json` con Twig.
- Se eliminaron `Services/` y `Controllers/Api/` de la propuesta inicial: la transacción de puntos va en `SolicitudTruequeDao` y las respuestas JSON en los propios controladores.
- Aviso de una errata del enunciado: Composer es un gestor de dependencias, no de plantillas.

### Motivo
El profesor propone una estructura concreta, así que había que ajustar la propuesta a ella en lugar de usar una estructura propia.

### Qué he aprendido


---

## 3. Crear el repositorio en GitHub
**Fecha:** 29/10/2026

### Prompt
> Quiero subir el esqueleto del proyecto a un repositorio de GitHub. Explícame los pasos para crearlo correctamente (nombre, visibilidad, si debo inicializarlo con README o no) y yo subo el primer commit con la estructura.

### Resultado
- Pasos para crear el repositorio `proyecto-noviembre` como privado y vacío.
- Primer commit "Estructura inicial del proyecto" subido a la rama `main`.
- Explicación de por qué no conviene crear el repositorio con README si ya tienes commits en local.

### Motivo
Tener el proyecto versionado desde el principio para poder guardar el progreso, volver atrás si algo se rompe y entregarlo.

### Qué he aprendido


---

## 4. Dockerfile
**Fecha:** 30/10/2026

### Prompt
> Escríbeme un Dockerfile lo más sencillo posible para mi proyecto con PHP 8.2 y Apache que incluya: extensión PDO MySQL, Xdebug configurado desde `docker/xdebug.ini`, DocumentRoot apuntando a `public/` y que todas las rutas pasen por `index.php` para el Router. Comenta cada instrucción.

### Resultado
- Dockerfile mínimo basado en `php:8.2-apache`, con `pdo_mysql` y Xdebug instalados, `sed` para cambiar el DocumentRoot a `public/` y `FallbackResource /index.php` (que evita necesitar `.htaccess` y `mod_rewrite`).
- `xdebug.ini` preparado para VS Code (`client_host=host.docker.internal`, puerto 9003).
- La primera versión era más completa (Composer dentro del contenedor, paquetes del sistema). Se simplificó porque Composer ya está en el Mac y `vendor/` entra por el volumen.

### Motivo
El proyecto tiene que desplegarse con Docker y necesitaba la imagen de PHP + Apache configurada para la estructura del proyecto.

### Qué he aprendido


---

## 5. docker-compose.yml
**Fecha:** 30/10/2026

### Prompt
> Escríbeme un `docker-compose.yml` sencillo con dos servicios: la app (construida con `docker/Dockerfile`) y MySQL 8. Monta el código como volumen, carga automáticamente los `.sql` de la carpeta `database/` al crear la base de datos y pasa las credenciales a PHP mediante variables de entorno. Explícame cómo levantarlo.

### Resultado
- `docker-compose.yml` con los servicios `app` (puerto 8080) y `db` (MySQL 8.0, puerto 3306, volumen persistente).
- Los `.sql` de `database/` se montan en `/docker-entrypoint-initdb.d`.
- Las variables `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` se leen desde `Database.php` con `getenv()`.
- Avisos: los `.sql` solo se ejecutan la primera vez (para recargarlos hay que usar `docker compose down -v`, que borra los datos), y si el 3306 está ocupado hay que cambiar el puerto.

### Motivo
Levantar todo el entorno (servidor y base de datos) con un solo comando, como pide la entrega de infraestructura.

### Qué he aprendido

## 03/11/2026

### 1. Estructura de carpetas para las plantillas Twig

**Prompt:**
> ¿Dónde debo ubicar las plantillas Twig dentro de un proyecto PHP con arquitectura MVC y cómo las organizo (layout, partials, páginas y componentes para la parte SPA)?

**Resultado:**
Carpeta `templates/` en la raíz del proyecto, fuera de `public/`, dividida en `layout/`, `partials/`, `pages/`, `components/` y `admin/`. Configuración del `FilesystemLoader` de Twig apuntando a `../templates`.

**Motivo:**
Tener las plantillas fuera de la carpeta pública para que Apache no las sirva directamente y separar bien la capa de vista.

**Qué he aprendido:**


---

### 2. Ubicación de los archivos CSS y enlace desde Twig

**Prompt:**
> ¿Dónde se colocan los archivos CSS en un proyecto PHP con Twig y cómo se enlazan desde la plantilla base para que funcionen en todas las rutas?

**Resultado:**
CSS en `public/assets/css/` separado en `variables.css`, `base.css`, `layout.css`, `header.css`, etc. Enlazados en `base.html.twig` con rutas absolutas (`/assets/css/...`) y un `{% block styles %}` para que cada página añada su CSS.

**Motivo:**
El CSS sí tiene que ser accesible desde el navegador, a diferencia de las plantillas.

**Qué he aprendido:**


---

### 3. Error: el header no encontraba su CSS

**Prompt:**
> Mi `header.html` enlaza el CSS con `href="public/assets/css/header.css"` y no lo encuentra. ¿Cuál es el problema y cómo debería estructurarse un partial de Twig?

**Resultado:**
La ruta era relativa e incluía `public/`, que es la raíz web y no aparece en la URL. Ruta correcta: `/assets/css/header.css`. Además, un partial no debe llevar `<!DOCTYPE>`, `<head>` ni `<body>`; el `<link>` va en el layout base. Renombrado de los archivos a `.html.twig` y marcar `public/` como Resource Root en PhpStorm.

**Motivo:**
Corregir el error y no acabar con etiquetas `<html>` duplicadas al incluir los partials.

**Qué he aprendido:**


---

### 4. Plantillas base, header y footer

**Prompt:**
> Genera las plantillas Twig `base.html.twig`, `header.html.twig` y `footer.html.twig` para la aplicación de trueque de servicios. El header debe cambiar según haya sesión o no, mostrar el saldo de puntos, el enlace al panel solo para el rol ADMIN y tener menú hamburguesa en móvil.

**Resultado:**
Layout base con bloques `title`, `styles`, `content` y `scripts`. Header dinámico con las variables globales `usuario`, `pagina_actual` y `app_nombre`, saldo con `id="saldo-puntos"` para actualizarlo por JS sin recargar. Footer con enlaces y año automático. CSS del header y del footer y `header.js` para la hamburguesa.

**Motivo:**
Tener la estructura común de todas las páginas antes de empezar con las vistas concretas.

**Qué he aprendido:**


---

### 5. Front controller (index.php) para probar las vistas

**Prompt:**
> Crea un `public/index.php` mínimo que cargue Composer, inicie sesión, configure Twig y tenga un router sencillo para poder ver el header funcionando, con una forma temporal de simular un usuario logueado.

**Resultado:**
`index.php` con `session_start()`, Twig, variable global `usuario`, router con `switch` sobre la URL y modo demo temporal (`?demo`, `?demo=admin`, `?salir`). Página `pages/inicio.html.twig` de prueba y `.htaccess` para redirigir todas las rutas a `index.php`.

**Motivo:**
Poder ver las plantillas en el navegador antes de tener el login y los controladores.

**Qué he aprendido:**


---

### 6. Errores al levantar Docker

**Prompt:**
> Al ejecutar `docker compose up -d` obtengo varios errores: volumen `db_data` no definido, puerto 3306 ocupado y después "403 Forbidden" en Apache. ¿Cómo los soluciono?

**Resultado:**
- Declarar `volumes: db_data:` a nivel raíz del `docker-compose.yml`.
- Cambiar el puerto de MySQL a `3307:3306` porque el 3306 ya estaba en uso en el Mac.
- El 403 venía de que Apache servía la raíz del proyecto en vez de `public/`. Se soluciona copiando el `apache.conf` del profesor (DocumentRoot en `/var/www/html/public` y `AllowOverride All`) y activando `mod_rewrite` en el Dockerfile.
- Usar el `xdebug.ini` del profesor sin `docker-php-ext-enable` para no cargar Xdebug dos veces.
- Error "not found" en el build: el `apache.conf` tenía que estar dentro de la carpeta `docker/`.

**Motivo:**
Dejar el entorno Docker funcionando con la configuración proporcionada por el profesor.

**Qué he aprendido:**


---

### 7. Errores de Twig: plantillas no encontradas

**Prompt:**
> Twig lanza "Unable to find template layout/base.html.twig" y después lo mismo con `partials/footer.html.twig`. ¿Qué falta?

**Resultado:**
Faltaba crear `templates/layout/base.html.twig` y renombrar `footer.html` a `footer.html.twig`, dejándolo solo con la etiqueta `<footer>`.

**Motivo:**
Que la página de inicio se renderice completa.

**Qué he aprendido:**


---

### 8. Nombre de la aplicación

**Prompt:**
> Propón nombres para una aplicación web de trueque de servicios entre profesionales basada en puntos por hora.

**Resultado:**
Varias propuestas (TruequePro, HoraXHora, Banco de Oficios, Truekia...). Elegido **HoraXHora**. Se define una sola vez como variable global de Twig (`app_nombre`) y se usa en el título, el header y el footer.

**Motivo:**
Darle identidad al proyecto y no repetir el nombre en todas las plantillas.

**Qué he aprendido:**


---

### 9. Página de inicio y hojas de estilo

**Prompt:**
> Crea la página de inicio en Twig (hero, cómo funciona, categorías y llamada a la acción) y los archivos `variables.css`, `base.css`, `header.css`, `footer.css` e `inicio.css` usando variables CSS para los colores, `em` para tamaños de letra y `px` para espaciados, sin `rem`.

**Resultado:**
`inicio.html.twig` con categorías de ejemplo que luego se sustituirán por las del DAO. `variables.css` con la paleta en hexadecimal, `base.css` con reset, tipografía, footer pegado abajo (flex) y botones reutilizables. Todos los CSS convertidos a `em`/`px`. Los estilos de `.btn` se dejan solo en `base.css` para que funcionen en todas las páginas.

**Motivo:**
Cumplir el requisito de CSS3 nativo con variables y diseño adaptativo, y tener una portada para enseñar.

**Qué he aprendido:**


---

### 10. Avisos de SonarLint

**Prompt:**
> SonarLint me marca 7 avisos en Dockerfile, base.css, inicio.css e index.php. Explícame cada uno y cómo corregirlos.

**Resultado:**
- Dockerfile: unir los `RUN` en uno solo. El aviso de usuario root se marca como seguro por ser entorno de desarrollo.
- CSS: envolver `scroll-behavior: smooth` y las animaciones de hover en `prefers-reduced-motion` por accesibilidad.
- index.php: `require` → `require_once`, salto de línea al final del archivo; el TODO del modo demo se mantiene hasta hacer el login.

**Motivo:**
Mantener el código limpio y accesible de cara a la corrección.

**Qué he aprendido:**
---