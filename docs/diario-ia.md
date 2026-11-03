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