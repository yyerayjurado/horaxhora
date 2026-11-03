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
Que antes de ponerme a programar tengo que tener bien la base de datos, porque si una tabla está mal luego el error lo arrastras a los modelos y a los DAO. Y que no hay que repetir datos entre tablas: se enlazan con la clave y listo.

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
Que si el profe da una estructura hay que seguirla y no inventarse otra. Y que Composer no es un gestor de plantillas como pone el enunciado, es un gestor de dependencias: sirve para instalar librerías como Twig.

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
Que si ya tengo el proyecto en local no hay que crear el repo con README, porque luego da conflicto al hacer el primer push. Y que es mejor subirlo desde el principio para poder volver atrás si rompo algo.

---

## 4. Dockerfile
**Fecha:** 30/10/2026

### Prompt
> Escríbeme un Dockerfile lo más sencillo posible para mi proyecto con PHP 8.2 y Apache que incluya: extensión PDO MySQL, Xdebug configurado desde `../xdebug.ini`, DocumentRoot apuntando a `public/` y que todas las rutas pasen por `index.php` para el Router. Comenta cada instrucción.

### Resultado
- Dockerfile mínimo basado en `php:8.2-apache`, con `pdo_mysql` y Xdebug instalados, `sed` para cambiar el DocumentRoot a `public/` y `FallbackResource /index.php` (que evita necesitar `.htaccess` y `mod_rewrite`).
- `xdebug.ini` preparado para VS Code (`client_host=host.docker.internal`, puerto 9003).
- La primera versión era más completa (Composer dentro del contenedor, paquetes del sistema). Se simplificó porque Composer ya está en el Mac y `vendor/` entra por el volumen.

### Motivo
El proyecto tiene que desplegarse con Docker y necesitaba la imagen de PHP + Apache configurada para la estructura del proyecto.

### Qué he aprendido
Que el Dockerfile es como una receta para montar el servidor: partes de una imagen con PHP y Apache y le vas añadiendo lo que necesitas (PDO, Xdebug...). Y que cuanto más simple, mejor; no hay que meter cosas que no hacen falta.

---

## 5. docker-compose.yml
**Fecha:** 30/10/2026

### Prompt
> Escríbeme un `docker-compose.yml` sencillo con dos servicios: la app (construida con `../Dockerfile`) y MySQL 8. Monta el código como volumen, carga automáticamente los `.sql` de la carpeta `database/` al crear la base de datos y pasa las credenciales a PHP mediante variables de entorno. Explícame cómo levantarlo.

### Resultado
- `docker-compose.yml` con los servicios `app` (puerto 8080) y `db` (MySQL 8.0, puerto 3306, volumen persistente).
- Los `.sql` de `database/` se montan en `/docker-entrypoint-initdb.d`.
- Las variables `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` se leen desde `Database.php` con `getenv()`.
- Avisos: los `.sql` solo se ejecutan la primera vez (para recargarlos hay que usar `docker compose down -v`, que borra los datos), y si el 3306 está ocupado hay que cambiar el puerto.

### Motivo
Levantar todo el entorno (servidor y base de datos) con un solo comando, como pide la entrega de infraestructura.

### Qué he aprendido
Que con docker-compose levanto el servidor y la base de datos a la vez con un solo comando. Y que los .sql de `database/` solo se ejecutan la primera vez; si quiero que se vuelvan a cargar tengo que hacer `down -v`, que borra los datos.

---

## 03/11/2026

### 1. Estructura de carpetas para las plantillas Twig

**Prompt:**
> ¿Dónde debo ubicar las plantillas Twig dentro de un proyecto PHP con arquitectura MVC y cómo las organizo (layout, partials, páginas y componentes para la parte SPA)?

**Resultado:**
Carpeta `templates/` en la raíz del proyecto, fuera de `public/`, dividida en `layout/`, `partials/`, `pages/`, `components/` y `admin/`. Configuración del `FilesystemLoader` de Twig apuntando a `../templates`.

**Motivo:**
Tener las plantillas fuera de la carpeta pública para que Apache no las sirva directamente y separar bien la capa de vista.

**Qué he aprendido:**
Que las plantillas de Twig no van en `public/` porque nadie tiene que poder abrirlas desde el navegador, solo PHP. Y que conviene separarlas en layout, partials y pages para no repetir código.

---

### 2. Ubicación de los archivos CSS y enlace desde Twig

**Prompt:**
> ¿Dónde se colocan los archivos CSS en un proyecto PHP con Twig y cómo se enlazan desde la plantilla base para que funcionen en todas las rutas?

**Resultado:**
CSS en `public/assets/css/` separado en `variables.css`, `base.css`, `layout.css`, `header.css`, etc. Enlazados en `base.html.twig` con rutas absolutas (`/assets/css/...`) y un `{% block styles %}` para que cada página añada su CSS.

**Motivo:**
El CSS sí tiene que ser accesible desde el navegador, a diferencia de las plantillas.

**Qué he aprendido:**
Que el CSS, al revés que las plantillas, sí va dentro de `public/` porque el navegador lo tiene que descargar. Y que con el `block styles` cada página puede meter su propio CSS sin tocar la base.

---

### 3. Error: el header no encontraba su CSS

**Prompt:**
> Mi `header.html` enlaza el CSS con `href="public/assets/css/header.css"` y no lo encuentra. ¿Cuál es el problema y cómo debería estructurarse un partial de Twig?

**Resultado:**
La ruta era relativa e incluía `public/`, que es la raíz web y no aparece en la URL. Ruta correcta: `/assets/css/header.css`. Además, un partial no debe llevar `<!DOCTYPE>`, `<head>` ni `<body>`; el `<link>` va en el layout base. Renombrado de los archivos a `.html.twig` y marcar `public/` como Resource Root en PhpStorm.

**Motivo:**
Corregir el error y no acabar con etiquetas `<html>` duplicadas al incluir los partials.

**Qué he aprendido:**
Que `public/` no aparece en la URL porque es la raíz de la web, y que las rutas tienen que empezar por `/` para que no se rompan en otras páginas. También que un partial es solo un trozo de página, sin `html`, `head` ni `body`.

---

### 4. Plantillas base, header y footer

**Prompt:**
> Genera las plantillas Twig `base.html.twig`, `header.html.twig` y `footer.html.twig` para la aplicación de trueque de servicios. El header debe cambiar según haya sesión o no, mostrar el saldo de puntos, el enlace al panel solo para el rol ADMIN y tener menú hamburguesa en móvil.

**Resultado:**
Layout base con bloques `title`, `styles`, `content` y `scripts`. Header dinámico con las variables globales `usuario`, `pagina_actual` y `app_nombre`, saldo con `id="saldo-puntos"` para actualizarlo por JS sin recargar. Footer con enlaces y año automático. CSS del header y del footer y `header.js` para la hamburguesa.

**Motivo:**
Tener la estructura común de todas las páginas antes de empezar con las vistas concretas.

**Qué he aprendido:**
Que con `extends` y `block` hago una plantilla base y las demás páginas solo rellenan los huecos. Y que con un `if` en Twig puedo enseñar cosas distintas según si hay sesión o si el usuario es admin.

---

### 5. Front controller (index.php) para probar las vistas

**Prompt:**
> Crea un `public/index.php` mínimo que cargue Composer, inicie sesión, configure Twig y tenga un router sencillo para poder ver el header funcionando, con una forma temporal de simular un usuario logueado.

**Resultado:**
`index.php` con `session_start()`, Twig, variable global `usuario`, router con `switch` sobre la URL y modo demo temporal (`?demo`, `?demo=admin`, `?salir`). Página `pages/inicio.html.twig` de prueba y `.htaccess` para redirigir todas las rutas a `index.php`.

**Motivo:**
Poder ver las plantillas en el navegador antes de tener el login y los controladores.

**Qué he aprendido:**
Que `index.php` es la única puerta de entrada a la web y desde ahí se decide qué página enseñar según la URL. Y que puedo simular un usuario logueado para probar cosas antes de tener el login hecho.

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
Que los volúmenes con nombre hay que declararlos abajo del todo en el docker-compose, que si un puerto está pillado lo cambio por fuera (3307) sin tocar nada por dentro, y que el 403 era porque Apache no apuntaba a `public/`. Leyendo bien el error casi siempre sabes dónde está el fallo.

---

### 7. Errores de Twig: plantillas no encontradas

**Prompt:**
> Twig lanza "Unable to find template layout/base.html.twig" y después lo mismo con `partials/footer.html.twig`. ¿Qué falta?

**Resultado:**
Faltaba crear `templates/layout/base.html.twig` y renombrar `footer.html` a `footer.html.twig`, dejándolo solo con la etiqueta `<footer>`.

**Motivo:**
Que la página de inicio se renderice completa.

**Qué he aprendido:**
Que Twig busca los archivos con el nombre exacto, así que si no acaban en `.html.twig` no los encuentra. Los errores de Twig te dicen qué archivo falta y en qué línea; solo hay que leerlos.

---

### 8. Nombre de la aplicación

**Prompt:**
> Propón nombres para una aplicación web de trueque de servicios entre profesionales basada en puntos por hora.

**Resultado:**
Varias propuestas (TruequePro, HoraXHora, Banco de Oficios, Truekia...). Elegido **HoraXHora**. Se define una sola vez como variable global de Twig (`app_nombre`) y se usa en el título, el header y el footer.

**Motivo:**
Darle identidad al proyecto y no repetir el nombre en todas las plantillas.

**Qué he aprendido:**
Que con `addGlobal` pongo una variable disponible en todas las plantillas, así si cambio el nombre de la app solo lo toco en un sitio.

---

### 9. Página de inicio y hojas de estilo

**Prompt:**
> Crea la página de inicio en Twig (hero, cómo funciona, categorías y llamada a la acción) y los archivos `variables.css`, `base.css`, `header.css`, `footer.css` e `inicio.css` usando variables CSS para los colores, `em` para tamaños de letra y `px` para espaciados, sin `rem`.

**Resultado:**
`inicio.html.twig` con categorías de ejemplo que luego se sustituirán por las del DAO. `variables.css` con la paleta en hexadecimal, `base.css` con reset, tipografía, footer pegado abajo (flex) y botones reutilizables. Todos los CSS convertidos a `em`/`px`. Los estilos de `.btn` se dejan solo en `base.css` para que funcionen en todas las páginas.

**Motivo:**
Cumplir el requisito de CSS3 nativo con variables y diseño adaptativo, y tener una portada para enseñar.

**Qué he aprendido:**
Que las variables CSS se definen una vez en `:root` y se usan con `var()`, así cambio un color y cambia en toda la web. También la diferencia entre `em`, `rem` y `px`, y que los estilos que se usan en todas las páginas van en `base.css`.

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
Que lo de SonarLint no son errores, son avisos para dejar el código más limpio. Lo de `prefers-reduced-motion` no lo conocía: hay gente que tiene quitadas las animaciones en su sistema y la web lo tiene que respetar.

---

## 03/11/2026 · 12:31

### 11. Script de la base de datos a partir del modelo E-R

**Prompt:**
> A partir del modelo Entidad-Relación y el script SQL del anexo del enunciado, genera la estructura de la base de datos en MySQL y unos datos de prueba coherentes. Sepáralo en dos archivos, `01_schema.sql` (tablas) y `02_seeds.sql` (datos), para que Docker los ejecute en orden al crear el contenedor.

**Resultado:**
- `01_schema.sql` con las 6 tablas del anexo y restricciones `CHECK` añadidas (saldo no negativo, horas y tarifas mayores que 0, puntuación de 1 a 5) y un índice por estado en las solicitudes.
- `02_seeds.sql` con 1 administrador, 4 profesionales, 4 categorías, 8 servicios, 8 ofertas (una desactivada), una solicitud de cada estado y valoraciones solo en las completadas. Los saldos cuadran con los trueques completados. Contraseña de prueba `1234` guardada con bcrypt para que funcione con `password_verify`.
- `SET NAMES utf8mb4` al principio para que los acentos se importen bien.
- Aviso: el `ON DELETE CASCADE` del anexo borra el historial de trueques si se elimina un usuario. Para la moderación conviene dar de baja con un campo `activo` en vez de un `DELETE`.

**Motivo:**
Tener la base de datos creada y con datos para poder probar el resto de la aplicación, y cumplir el entregable del script `.sql` con seeds.

**Qué he aprendido:**
Que con `CHECK` la base de datos no deja meter datos raros (saldo negativo, una puntuación de 7...). Que las contraseñas nunca se guardan tal cual, van hasheadas. Y que el `ON DELETE CASCADE` te puede borrar más cosas de las que crees.

---

### 12. Qué son los seeds

**Prompt:**
> ¿Qué son los "seeds" de una base de datos y para qué sirven en este proyecto?

**Resultado:**
Son los datos de prueba iniciales que se insertan al crear la base de datos. Separan el *contenido* (`02_seeds.sql`) de la *estructura* (`01_schema.sql`), permiten probar la aplicación nada más levantarla, facilitan la corrección al profesor y se pueden restaurar con `docker compose down -v` + `up -d`.

**Motivo:**
Entender un término que pide el enunciado en los entregables.

**Qué he aprendido:**
Que los seeds son los datos de prueba para que la base de datos no esté vacía al arrancar. Así el profe puede probarlo todo sin tener que crear usuarios a mano.

---

### 13. Conectar PhpStorm a la base de datos de Docker

**Prompt:**
> ¿Qué datos tengo que poner en "Data Sources and Drivers" de PhpStorm para conectarme a la base de datos MySQL que corre en Docker?

**Resultado:**
Host `localhost`, puerto el publicado en `docker-compose.yml`, usuario y contraseña `usuario`, base de datos `sistema_trueque`. Descargar antes el driver de MySQL y comprobar con *Test Connection*. Soluciones a los errores típicos (contenedor parado, acceso denegado, `allowPublicKeyRetrieval`).

**Motivo:**
Poder ver y consultar las tablas desde el IDE.

**Qué he aprendido:**
Que desde fuera de Docker me conecto con `localhost` y el puerto que he publicado, pero la app por dentro usa el nombre del servicio (`db`). Y que sin el driver descargado PhpStorm no puede conectar.

---

### 14. Conexión PDO a la base de datos

**Prompt:**
> Explícame cómo crear la conexión a la base de datos con PDO usando el patrón Singleton dentro de la capa DAO, leyendo los datos de conexión de las variables de entorno de Docker y cargando las clases con el autoload de Composer.

**Resultado:**
- `src/Dao/BD.php`: clase `final` con constructor privado y método estático `getConexion()` que crea la conexión PDO una sola vez. Lee `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` con `getenv()`, usa `charset=utf8mb4` y activa excepciones, `FETCH_ASSOC` y consultas preparadas reales. Si falla, lanza un error genérico sin mostrar datos del servidor.
- `composer.json` con autoload PSR-4 (`App\` → `src/`) y `composer dump-autoload`.
- `public/.htaccess` para que todas las rutas pasen por `index.php`.
- Ruta temporal `/test-bd` en `index.php`. Resultado: conexión correcta con los 5 usuarios de los seeds.

**Uso de la IA:**
En un primer momento la IA escribió los archivos directamente en mi proyecto sin que se lo pidiera. Le pedí que lo deshiciera, me pasó el código en el chat y lo incorporé yo a mano.

**Motivo:**
Tener la conexión que usarán todos los DAO antes de empezar con los modelos.

**Qué he aprendido:**
Que con el Singleton solo se crea una conexión y todos los DAO usan la misma. Que las contraseñas de la base de datos no van escritas en el código, se leen de las variables de entorno. Que con el autoload de Composer no hace falta hacer `require` de cada clase. Y que tengo que vigilar lo que hace la IA y decidir yo qué entra en mi proyecto.

---

### 15. Salto de línea al final de los archivos

**Prompt:**
> SonarLint me sigue marcando "Add a new line at the end of this file" en `index.php`. ¿Cómo lo arreglo y cómo hago que PhpStorm lo haga solo al guardar?

**Resultado:**
Al pegar código, PhpStorm elimina la última línea vacía. Se arregla añadiéndola a mano, y de forma permanente activando *Settings → Editor → General → On Save → "Ensure every saved file ends with a line break"*.

**Motivo:**
Quitar el aviso de SonarLint y no tener que corregirlo en cada archivo.

**Qué he aprendido:**
Que PhpStorm se puede configurar para que haga cosas solo al guardar, como dejar la línea vacía del final, y así me ahorro corregirlo a mano cada vez.

---

### 16. Commit y push de los cambios

**Prompt:**
> Dame los comandos para hacer commit y push de los cambios de hoy, con mensajes de commit claros.

**Resultado:**
Comandos `git add`, `git commit` y `git push`, con la recomendación de revisar antes `git status` y la opción de separar los cambios en varios commits (base de datos, conexión, rutas) para que el historial sea más claro.

**Motivo:**
Subir el trabajo al repositorio de GitHub.

**Qué he aprendido:**
Que antes de hacer commit es mejor mirar `git status` para ver qué se va a subir, y que poner `feat:`, `docs:` o `chore:` en los mensajes hace que el historial se entienda mejor.

---

### 17. Diagrama de la arquitectura MVC

**Prompt:**
> Genera una imagen de la arquitectura MVC + DAO de mi proyecto con las carpetas reales, organizada en capas apiladas (Vista, Controlador, Modelo) y marcando qué partes están hechas y cuáles pendientes.

**Resultado:**
Diagrama en PNG y SVG con el navegador arriba, las capas Vista (`templates/`, `public/assets/`), Control (entrada, Router, controladores de páginas y API) y Modelo (`src/Models/`, `src/Dao/`), y MySQL abajo. Lo pendiente aparece con borde discontinuo. Una primera versión con flujo numerado se descartó por tener demasiadas flechas.

**Nota:**
Es un diagrama de arquitectura, no el diagrama de clases MVC que pide el enunciado. Ese se hará cuando estén los modelos y los DAO.

**Motivo:**
Tener una visión general del proyecto para la documentación.

**Qué he aprendido:**
Que un diagrama de arquitectura y uno de clases no son lo mismo: uno enseña cómo va una petición por las capas y el otro las clases con sus atributos y métodos. Y que en MVC cada capa tiene su trabajo: la vista enseña, el controlador decide y el modelo se encarga de los datos.

---
