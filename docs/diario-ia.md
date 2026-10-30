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


---