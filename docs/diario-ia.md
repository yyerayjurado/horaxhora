# Diario de uso de IA

Registro de las consultas hechas a la IA durante el desarrollo del proyecto: qué se pidió, qué se obtuvo, por qué se pidió y qué se ha aprendido.

---

## 1. Revisión del diagrama E/R y propuesta de estructura de carpetas
**Fecha:** 29/10/2026

**Prompt:**
> Te adjunto el diagrama Entidad-Relación de mi proyecto (trueque de servicios). Revísalo y dime qué errores de diseño tiene (nombres de entidades, claves, tipos de datos, relaciones y datos duplicados). Después, a partir del modelo corregido, propón una estructura de carpetas para una aplicación PHP con MVC + DAO, plantillas Twig, JavaScript con fetch y despliegue en Docker, explicando brevemente para qué sirve cada carpeta.

**Resultado:**
- Lista de correcciones al diagrama: `catalogo` pasa a ser `categoria`; en `oferta servicio` sobran los campos copiados de `servicio` y falta el `UNIQUE (id_profesional, id_servicio)`; el trueque debe apuntar a `id_oferta` en lugar de copiar datos; `valoraciones` debe enlazar con `id_solicitud` (relación 1:1); y hay tipos mal elegidos (`estado` como ENUM, confirmaciones como BOOLEAN, `telefono` como VARCHAR, `puntuacion` como TINYINT 1–5).
- Propuesta de estructura de carpetas con `public/` como única carpeta accesible, capas Models / DAO / Controllers, vistas Twig y módulos JS.

**Motivo:**
Antes de empezar a programar quería asegurarme de que la base de datos era correcta, porque de cada tabla sale un Modelo y un DAO. Si el diseño está mal, todo lo demás estará mal.

**Qué he aprendido:**
Que antes de ponerme a programar tengo que tener bien la base de datos, porque si una tabla está mal luego el error lo arrastras a los modelos y a los DAO. Y que no hay que repetir datos entre tablas: se enlazan con la clave y listo.

---

## 2. Adaptar la estructura a la plantilla del profesor
**Fecha:** 29/10/2026

**Prompt:**
> Te adjunto un .zip con la estructura de carpetas que nos ha dado el profesor. Adapta tu propuesta anterior a esa plantilla respetando sus nombres (`Dao`, `Views` dentro de `src`, `App.php`, `Autoloader.php`, `Router.php`), añade solo lo que falte para cumplir el enunciado y genera el esqueleto con los archivos vacíos.

**Resultado:**
- Esqueleto del proyecto respetando la plantilla y añadiendo `database/` (SQL de esquema y datos de prueba), `docs/`, `public/assets/` (CSS y módulos JS) y `composer.json` con Twig.
- Aviso de una errata del enunciado: Composer es un gestor de dependencias, no de plantillas.

**Motivo:**
El profesor propone una estructura concreta, así que había que ajustar la propuesta a ella en lugar de usar una estructura propia.

**Qué he aprendido:**
Que si el profe da una estructura hay que seguirla y no inventarse otra. Y que Composer no es un gestor de plantillas como pone el enunciado, es un gestor de dependencias: sirve para instalar librerías como Twig.

---

## 3. Repositorio en GitHub
**Fecha:** 29/10/2026

**Prompt:**
> Quiero versionar el proyecto en GitHub desde el principio. ¿Cómo debo crear el repositorio (nombre, visibilidad, inicialización) si ya tengo el esqueleto en local, para evitar conflictos en el primer push?

**Resultado:**
- Repositorio `proyecto-noviembre` privado y vacío, con el primer commit "Estructura inicial del proyecto" en `main`.
- Explicación de por qué no conviene crear el repositorio con README si ya tienes commits en local.

**Motivo:**
Tener el proyecto versionado desde el principio para poder guardar el progreso, volver atrás si algo se rompe y entregarlo.

**Qué he aprendido:**
Que si ya tengo el proyecto en local no hay que crear el repo con README, porque luego da conflicto al hacer el primer push. Y que es mejor subirlo desde el principio para poder volver atrás si rompo algo.

---

## 4. Entorno Docker (Dockerfile y docker-compose)
**Fecha:** 30/10/2026 – 03/11/2026

**Prompt:**
> Necesito el entorno Docker del proyecto lo más sencillo posible: un Dockerfile con PHP 8.2 + Apache, PDO MySQL, Xdebug (desde `xdebug.ini`), DocumentRoot en `public/` y todas las rutas hacia `index.php`; y un `docker-compose.yml` con la app y MySQL 8 que monte el código como volumen, cargue los `.sql` de `database/` al crear la base de datos y pase las credenciales a PHP por variables de entorno. Después, integra el `apache.conf` y el `xdebug.ini` que nos ha dado el profesor.

**Resultado:**
- Dockerfile basado en `php:8.2-apache` con `pdo_mysql`, Xdebug y `mod_rewrite`. Se simplificó una primera versión más completa porque Composer ya está en el Mac y `vendor/` entra por el volumen.
- `docker-compose.yml` con los servicios `app` (puerto 8080) y `db` (MySQL 8.0, volumen persistente `db_data`). Los `.sql` se montan en `/docker-entrypoint-initdb.d` y las credenciales se leen con `getenv()`.
- Ajustes al integrar la configuración del profesor: declarar el volumen con nombre a nivel raíz, publicar MySQL en otro puerto porque el 3306 estaba ocupado en el Mac, y usar su `apache.conf` (DocumentRoot en `public/` y `AllowOverride All`) para resolver un 403.

**Motivo:**
El proyecto tiene que desplegarse con Docker y quería levantar el servidor y la base de datos con un solo comando, como pide la entrega de infraestructura.

**Qué he aprendido:**
Que el Dockerfile es como una receta para montar el servidor: partes de una imagen con PHP y Apache y le vas añadiendo lo que necesitas (PDO, Xdebug...), y cuanto más simple, mejor. Que con docker-compose levanto el servidor y la base de datos a la vez, y que los .sql de `database/` solo se ejecutan la primera vez; si quiero que se vuelvan a cargar tengo que hacer `down -v`, que borra los datos. Que los volúmenes con nombre se declaran abajo del todo, que si un puerto está pillado lo cambio por fuera sin tocar nada por dentro, y que leyendo bien el error casi siempre sabes dónde está el fallo.

---

## 5. Organización de las plantillas Twig y los CSS
**Fecha:** 03/11/2026

**Prompt:**
> ¿Dónde deben ubicarse las plantillas Twig y los archivos CSS en un proyecto PHP con MVC? Quiero las plantillas organizadas en layout, partials y páginas, y los CSS enlazados desde la plantilla base de forma que funcionen en cualquier ruta y que cada página pueda añadir el suyo.

**Resultado:**
- Plantillas en `templates/` (fuera de `public/`), divididas en `layout/`, `partials/` y `pages/`, con el `FilesystemLoader` de Twig apuntando a `../templates`.
- CSS en `public/assets/css/`, enlazado en `base.html.twig` con rutas absolutas (`/assets/css/...`) y un `{% block styles %}` por página.
- Un partial no lleva `<!DOCTYPE>`, `<head>` ni `<body>`, y las rutas no incluyen `public/` porque es la raíz web.

**Motivo:**
Separar bien la capa de vista y que Apache no pueda servir las plantillas directamente.

**Qué he aprendido:**
Que las plantillas de Twig no van en `public/` porque nadie tiene que poder abrirlas desde el navegador, solo PHP, y que el CSS al revés sí va dentro porque el navegador lo tiene que descargar. Que `public/` no aparece en la URL porque es la raíz de la web, y que las rutas tienen que empezar por `/` para que no se rompan en otras páginas. También que un partial es solo un trozo de página, sin `html`, `head` ni `body`, y que Twig busca los archivos con el nombre exacto.

---

## 6. Plantilla base, header y footer
**Fecha:** 03/11/2026

**Prompt:**
> Genera las plantillas Twig `base.html.twig`, `header.html.twig` y `footer.html.twig` para la aplicación de trueque de servicios. El header debe cambiar según haya sesión o no, mostrar el saldo de puntos, el enlace al panel solo para el rol ADMIN y tener menú hamburguesa en móvil. El nombre de la aplicación tiene que definirse en un único sitio.

**Resultado:**
- Layout base con bloques `title`, `styles`, `content` y `scripts`.
- Header dinámico con las variables globales `usuario`, `pagina_actual` y `app_nombre`, y el saldo con `id="saldo-puntos"` para actualizarlo por JS sin recargar.
- El nombre elegido para la aplicación, **HoraXHora**, se define una sola vez con `addGlobal`.

**Motivo:**
Tener la estructura común de todas las páginas antes de empezar con las vistas concretas.

**Qué he aprendido:**
Que con `extends` y `block` hago una plantilla base y las demás páginas solo rellenan los huecos. Que con un `if` en Twig puedo enseñar cosas distintas según si hay sesión o si el usuario es admin. Y que con `addGlobal` pongo una variable disponible en todas las plantillas, así si cambio el nombre de la app solo lo toco en un sitio.

---

## 7. Front controller provisional
**Fecha:** 03/11/2026

**Prompt:**
> Crea un `public/index.php` mínimo que cargue Composer, inicie sesión, configure Twig y enrute con un `switch` sobre la URL, con un modo temporal para simular un usuario logueado y poder probar las plantillas antes de tener el login.

**Resultado:**
`index.php` con `session_start()`, Twig, variable global `usuario`, router con `switch` y modo demo temporal (`?demo`, `?demo=admin`, `?salir`), más `.htaccess` para redirigir todas las rutas a `index.php`.

**Motivo:**
Poder ver las plantillas en el navegador antes de tener el login y los controladores.

**Qué he aprendido:**
Que `index.php` es la única puerta de entrada a la web y desde ahí se decide qué página enseñar según la URL. Y que puedo simular un usuario logueado para probar cosas antes de tener el login hecho.

---

## 8. Página de inicio y sistema de estilos
**Fecha:** 03/11/2026

**Prompt:**
> Crea la página de inicio en Twig (hero, cómo funciona, categorías y llamada a la acción) y los archivos `variables.css`, `base.css`, `header.css`, `footer.css` e `inicio.css` usando variables CSS para los colores, `em` para tamaños de letra y `px` para espaciados.

**Resultado:**
`inicio.html.twig` con categorías de ejemplo que luego se sustituirán por las del DAO. `variables.css` con la paleta, `base.css` con reset, tipografía, footer pegado abajo con flex y botones reutilizables (`.btn`) para todas las páginas.

**Motivo:**
Cumplir el requisito de CSS3 nativo con variables y diseño adaptativo, y tener una portada para enseñar.

**Qué he aprendido:**
Que las variables CSS se definen una vez en `:root` y se usan con `var()`, así cambio un color y cambia en toda la web. También la diferencia entre `em`, `rem` y `px`, y que los estilos que se usan en todas las páginas van en `base.css`.

---

## 9. Calidad de código con SonarLint
**Fecha:** 03/11/2026

**Prompt:**
> SonarLint me marca varios avisos en el Dockerfile, los CSS y el `index.php`. Explícame cada uno, cómo corregirlo y cómo configurar PhpStorm para evitar los que se repiten al guardar.

**Resultado:**
- Dockerfile: unir los `RUN` en uno solo. El aviso de usuario root se marca como seguro por ser entorno de desarrollo.
- CSS: envolver `scroll-behavior: smooth` y las animaciones en `prefers-reduced-motion` por accesibilidad.
- PHP: `require` → `require_once` y salto de línea al final del archivo, que se automatiza con *Settings → Editor → General → On Save → "Ensure every saved file ends with a line break"*.

**Motivo:**
Mantener el código limpio y accesible de cara a la corrección.

**Qué he aprendido:**
Que lo de SonarLint no son errores, son avisos para dejar el código más limpio. Lo de `prefers-reduced-motion` no lo conocía: hay gente que tiene quitadas las animaciones en su sistema y la web lo tiene que respetar. Y que PhpStorm se puede configurar para que haga cosas solo al guardar, como dejar la línea vacía del final.

---

## 10. Base de datos: script, seeds y conexión desde el IDE
**Fecha:** 03/11/2026

**Prompt:**
> A partir del modelo E-R y el script SQL del anexo del enunciado, genera la estructura de la base de datos en MySQL y unos datos de prueba coherentes, separados en `01_schema.sql` y `02_seeds.sql` para que Docker los ejecute en orden. Explícame también qué son los seeds y cómo conectar PhpStorm a esta base de datos.

**Resultado:**
- `01_schema.sql` con las 6 tablas del anexo, restricciones `CHECK` (saldo no negativo, horas y tarifas mayores que 0, puntuación de 1 a 5) y un índice por estado en las solicitudes.
- `02_seeds.sql` con 1 administrador, 4 profesionales, categorías, servicios, ofertas (una desactivada), una solicitud de cada estado y valoraciones solo en las completadas. Los saldos cuadran con los trueques completados y la contraseña de prueba está guardada con bcrypt.
- `SET NAMES utf8mb4` para que los acentos se importen bien.
- Aviso: el `ON DELETE CASCADE` del anexo borra el historial de trueques si se elimina un usuario.
- Conexión desde PhpStorm con `localhost` y el puerto publicado, frente a `db` desde dentro de Docker.

**Motivo:**
Tener la base de datos creada y con datos para probar el resto de la aplicación, y cumplir el entregable del script `.sql` con seeds.

**Qué he aprendido:**
Que con `CHECK` la base de datos no deja meter datos raros (saldo negativo, una puntuación de 7...). Que las contraseñas nunca se guardan tal cual, van hasheadas. Que el `ON DELETE CASCADE` te puede borrar más cosas de las que crees. Que los seeds son los datos de prueba para que la base de datos no esté vacía al arrancar, así el profe puede probarlo todo sin crear usuarios a mano. Y que desde fuera de Docker me conecto con `localhost`, pero la app por dentro usa el nombre del servicio (`db`).

---

## 11. Conexión PDO con Singleton
**Fecha:** 03/11/2026

**Prompt:**
> Explícame cómo crear la conexión a la base de datos con PDO usando el patrón Singleton dentro de la capa DAO, leyendo los datos de conexión de las variables de entorno de Docker y cargando las clases con el autoload de Composer.

**Resultado:**
- `src/Dao/BD.php`: clase `final` con constructor privado y método estático `getConexion()` que crea la conexión una sola vez, con `utf8mb4`, excepciones activadas y un error genérico si falla (sin mostrar datos del servidor).
- `composer.json` con autoload PSR-4 (`App\` → `src/`).
- Ruta temporal `/test-bd` para comprobarlo con los usuarios de los seeds.

**Uso de la IA:**
En un primer momento la IA escribió los archivos directamente en mi proyecto sin que se lo pidiera. Le pedí que lo deshiciera, me pasó el código en el chat y lo incorporé yo a mano.

**Motivo:**
Tener la conexión que usarán todos los DAO antes de empezar con los modelos.

**Qué he aprendido:**
Que con el Singleton solo se crea una conexión y todos los DAO usan la misma. Que las contraseñas de la base de datos no van escritas en el código, se leen de las variables de entorno. Que con el autoload de Composer no hace falta hacer `require` de cada clase. Y que tengo que vigilar lo que hace la IA y decidir yo qué entra en mi proyecto.

---

## 12. Diagrama de la arquitectura MVC
**Fecha:** 03/11/2026

**Prompt:**
> Genera una imagen de la arquitectura MVC + DAO de mi proyecto con las carpetas reales, organizada en capas apiladas (Vista, Controlador, Modelo) y marcando qué partes están hechas y cuáles pendientes.

**Resultado:**
Diagrama en PNG y SVG con el navegador arriba, las capas Vista, Control y Modelo, y MySQL abajo. Lo pendiente aparece con borde discontinuo.

**Nota:**
Es un diagrama de arquitectura, no el diagrama de clases MVC que pide el enunciado. Ese se hará cuando estén los modelos y los DAO.

**Motivo:**
Tener una visión general del proyecto para la documentación.

**Qué he aprendido:**
Que un diagrama de arquitectura y uno de clases no son lo mismo: uno enseña cómo va una petición por las capas y el otro las clases con sus atributos y métodos. Y que en MVC cada capa tiene su trabajo: la vista enseña, el controlador decide y el modelo se encarga de los datos.

---

## 13. Login con sesiones y consultas seguras
**Fecha:** 03/11/2026

**Prompt:**
> Implementa el login siguiendo el estilo visto en clase (formulario con POST normal, `$_SESSION`, `header("Location")` y `session_destroy()` para el logout), adaptado a mi estructura MVC + DAO con Twig y comprobando el usuario contra la base de datos. Las consultas del DAO deben seguir el formato de clase: `try/catch` con `PDOException`, `prepare()` cuando hay datos del usuario y `fetch(PDO::FETCH_ASSOC)`.

**Resultado:**
- `AuthController` con `login()` (GET muestra, POST procesa) y `logout()`, `UsuarioDao` con `buscarPorEmail()` y la plantilla `login.html.twig`.
- La contraseña se comprueba con `password_verify()` y en la sesión solo se guardan id, nombre, rol y puntos.
- Las consultas usan `BD::getConexion()` y `prepare()` para evitar inyección SQL.
- Se descartaron otras propuestas (con `fetch` y CSRF) porque no seguían lo visto en clase.

**Motivo:**
Tener la autenticación funcionando, que es la base de las partes privadas (agenda, ofertas, panel admin), y que todas las consultas sigan el mismo patrón.

**Qué he aprendido:**
Que la contraseña no se compara con `===` porque en la base de datos está guardada con hash, y que para eso está `password_verify()`. Que en la sesión nunca se guarda la contraseña, solo los datos que necesito enseñar, y que con `header('Location')` y `exit()` mando al usuario a otra página. Que cuando el dato viene del usuario hay que usar `prepare()` y no `query()`, porque si no me pueden hacer inyección SQL. Y que el operador `?:` es una forma corta de un `if`.

---

## 14. Autoload, tipado estricto y revisión de la estructura
**Fecha:** 04/11/2026

**Prompt:**
> Analiza la estructura de carpetas actual sin hacer cambios y dime si funciona. Explícame la diferencia entre PSR-4, el autoload y el router, qué hace `declare(strict_types=1)`, si hace falta `Autoloader.php` teniendo el de Composer y por qué me aparece `Class "App\Controllers\AuthController" not found`.

**Resultado:**
- El error venía de que `AuthController.php` solo tenía el `namespace`, sin la clase, y de que `UsuarioDAO.php` no coincidía en mayúsculas con la clase `UsuarioDao` (en Linux no se cargaría).
- PSR-4 es la norma que relaciona namespace con carpeta y archivo; el autoload de Composer la aplica; el router decide qué ejecutar según la URL.
- `strict_types` se mantiene porque el enunciado pide tipado estricto.
- Se borran `App.php` y `Autoloader.php`, que estaban vacíos y duplicarían el autoload de Composer. Se añade Composer al Dockerfile para quien clone el repositorio.

**Motivo:**
Entender el código que uso, no romper requisitos del enunciado y comprobar que la organización es correcta antes de seguir.

**Qué he aprendido:**
Que el error `Class not found` casi siempre es porque el nombre de la carpeta, del archivo o del `namespace` no coincide. Que PSR-4 es la regla que dice que el `namespace` tiene que coincidir con la carpeta y el nombre de la clase con el del archivo, y que en Mac funciona aunque las mayúsculas no coincidan, pero en Linux no. Que `declare(strict_types=1)` hace que PHP no cambie los tipos solo, por eso los datos de la URL hay que pasarlos a `(int)`. Y que no hace falta un autoloader propio si ya uso el de Composer.

---

## 15. Router en una clase siguiendo el modelo del profesor
**Fecha:** 06/11/2026

**Prompt:**
> Este es el `index.php` y el `Router.php` de mi profesor. Adapta mi enrutado a su forma de hacerlo (una clase con `dispatch()` y un `switch` por segmentos de la URL), con mis rutas actuales y dejándolo en `src/Router.php`, sin complicarlo.

**Resultado:**
`index.php` reducido a cargar el autoload, crear el `Router` y llamar a `dispatch()`. La clase `Router` arranca la sesión, prepara Twig, parte la URL en segmentos con `explode` y decide con un `switch` qué hacer en cada ruta, con `responderError()` para el 404. Se descartaron una versión con expresiones regulares y otra con todas las rutas futuras por ser innecesariamente complejas.

**Motivo:**
Seguir el mismo modelo que usa el profesor y dejar el `index.php` lo más limpio posible.

**Qué he aprendido:**
Que con `explode('/', trim($uri, '/'))` la URL se parte en trozos y con el primero sé a qué página quiere ir el usuario. Que con el `.htaccess` todas las URLs pasan por `index.php` y por eso puedo tener rutas amigables como `/login`. Y que es mejor ir añadiendo un `case` por cada página cuando la hago que meter de golpe rutas que todavía no existen.

---

## 16. Diseño del login y tipografía
**Fecha:** 06/11/2026

**Prompt:**
> Adapta este diseño de login (escrito en SCSS y con estilos globales en el `body`) a CSS3 nativo con mis variables y nomenclatura BEM, sin tocar el fondo de la página. Recomiéndame además una tipografía para HoraXHora y cómo aplicarla a toda la web.

**Resultado:**
- `auth.css` en CSS nativo con BEM (`auth`, `auth__input`, `auth__boton`...). Tras probar varias versiones se eligió una tarjeta centrada con campos subrayados y botón redondeado.
- Tipografía Plus Jakarta Sans (Google Fonts), cargada en el `<head>` antes de los CSS y aplicada desde una variable.

**Motivo:**
Que el login tenga un diseño propio y coherente con el resto de la web, sin frameworks ni preprocesadores, y unificar la letra de toda la web.

**Qué he aprendido:**
Que SCSS no es CSS normal (tiene `$variables` y anidación) y hay que pasarlo a CSS nativo. Que si pongo estilos en el `body` o en `*` dentro del CSS de una página, me puede romper el header y el footer de toda la web. Que una fuente de Google Fonts se carga con un `<link>` en el `<head>` y conviene ponerla en una variable. Y que si no veo los cambios del CSS muchas veces es la caché del navegador y se arregla con `Cmd + Shift + R`.

---

## 17. Página de perfil del profesional
**Fecha:** 10/11/2026

**Prompt:**
> Necesito la vista de perfil del profesional en Twig, extendiendo `layout/base.html.twig`. Debe mostrar los datos personales editables por formulario POST (siguiendo el patrón de clase: POST normal + `$_SESSION`), el saldo de puntos, la valoración media y las ofertas del usuario. Genera el `PerfilController`, un `PerfilDAO` con PDO (`try/catch` de `PDOException` y `fetch(PDO::FETCH_ASSOC)`) y la ruta `/perfil` en mi `Router`, con estilos en CSS nativo usando la paleta de `base.css`.

**Resultado:**
Controlador, DAO, plantilla y CSS del perfil. La valoración media se calcula con un JOIN entre `valoraciones`, `solicitudes_trueque` y `ofertas_profesionales`. Al integrarlo hubo que ajustar el namespace (`App\Dao`), la clave de sesión (`id`) y la ruta de la plantilla base. Además se detectó que `base.css` usaba `var(--radio)` sin tenerla definida.

**Motivo:**
El enunciado pide que cada profesional tenga un panel personal con sus puntos visibles en todo momento.

**Qué he aprendido:**

---

## 18. Registro en ventana modal reutilizable
**Fecha:** 11/11/2026

**Prompt:**
> Partiendo del ejemplo de modal visto en clase (`Modal` con `prototype`, `cargarURL` y `cargarLogica`), quiero convertir el registro en un modal. Requisitos: completar los métodos vacíos del ejemplo, reutilizar el mismo formulario en la página `/registro` y en el modal sin duplicar HTML, servir el fragmento desde una ruta PHP propia y que el JS del formulario funcione aunque se cargue varias veces. Valida en PHP los datos que en el JS no se comprobaban (contraseñas iguales, campos obligatorios).

**Resultado:**
- Clase `Modal` completa. El formulario pasa a un partial de Twig incluido por la página y por el fragmento `/registro/formulario`.
- `registro.js` encapsulado en una función para evitar redeclaraciones, y `?v=` en la URL del script para evitar la caché.
- Validación completa en `registroApi()`.
- Se detectaron dos fallos en el ejemplo original: `crearVelo()` no estaba definida y el CSS tenía propiedades duplicadas.

**Motivo:**
El enunciado valora la arquitectura híbrida con carga dinámica de componentes sin recargar la página.

**Qué he aprendido:**

---

## 19. Elección de servicios en el registro
**Fecha:** 11/11/2026

**Prompt:**
> En el registro, el profesional debe elegir una categoría y ver sus servicios cargados con `fetch` desde una API (`GET /api/servicios?categoria=`). Debe poder marcar servicios de varias categorías sin perder los ya elegidos, y los marcados deben verse siempre al final de la lista. Valida en PHP que los servicios existen. Diseño en dos columnas (datos / servicios) y chips en lugar de casillas.

**Resultado:**
- Endpoint en `CatalogoController` y `ServicioDao` con los métodos de catálogo.
- El JS guarda los elegidos en un objeto y repinta la lista en cada cambio.
- Formulario en dos columnas con CSS Grid y chips accesibles (casilla oculta pero navegable con teclado, estilo con `:has(input:checked)`).

**Motivo:**
Cada profesional solo debe poder ofertar servicios del catálogo oficial que realmente sabe hacer.

**Qué he aprendido:**

---

## 20. Imágenes en la landing
**Fecha:** 11/11/2026

**Prompt:**
> Integra cuatro imágenes en `inicio.html.twig` sin romper el diseño actual: una en el hero (dos columnas en escritorio, apilado en móvil) y una sección nueva con las otras tres en grid. Usa `loading="lazy"` en las que no se ven al cargar, textos alternativos descriptivos y respeta `prefers-reduced-motion`. Los botones de "Únete" deben abrir el modal de registro.

**Resultado:**
Hero en grid, sección "Profesionales como tú" con `figure`/`figcaption` y `aspect-ratio` + `object-fit`. Se corrigieron además dos detalles de `inicio.css`: faltaba `transition` en el hover de los pasos y el grid de categorías no tenía columnas.

**Motivo:**
Mejorar la presentación de la plataforma y la experiencia de usuario.

**Qué he aprendido:**

---

## 21. Panel de administración (SPA)
**Fecha:** 12/11/2026

**Prompt:**
> Diseña el panel de administración como una única página `/admin` con pestañas que cargan su contenido por `fetch` sin recargar. Cada sección usa una API REST bajo `/api/admin/...` con los métodos HTTP adecuados (GET listar, POST crear, PUT editar, PATCH cambios parciales, DELETE borrar), y todas comprueban en el servidor que el rol es ADMIN. Secciones: CRUD de categorías, CRUD de servicios con filtro por categoría, gestión de usuarios, historial de trueques con cancelación y moderación de reseñas. El HTML de cada sección en `<template>`.

**Resultado:**
`AdminController`, cuatro DAO nuevos (`CategoriaDao`, `TruequeDao`, `ValoracionDao`, `UsuarioAdminDao`), `admin.js` con funciones de ayuda compartidas y `admin.css`. Decisiones de diseño:
- "Dar de baja" desactiva al usuario (nueva columna `activo`) en vez de borrarlo, para no perder el historial por el `ON DELETE CASCADE`.
- El admin no puede darse de baja ni borrarse a sí mismo.
- Solo se cancelan trueques no terminados, comprobándolo en el propio `UPDATE`.
- La moderación permite quitar solo el comentario (sin alterar la media) o borrar la reseña completa.
- Trueques y reseñas se muestran como lista con resumen de cifras; el resto, en tablas.

**Motivo:**
Cubrir el apartado 3.6 del enunciado (Admin Dashboard).

**Qué he aprendido:**

---

## 22. Aprobación de registros por el administrador
**Fecha:** 12/11/2026

**Prompt:**
> Quiero que los registros nuevos queden pendientes hasta que el administrador los acepte o rechace. El usuario pendiente no puede iniciar sesión y sus ofertas no deben aparecer en el buscador. En el panel, un contador en la pestaña Usuarios y una lista de pendientes con los servicios que eligió cada uno.

**Resultado:**
Columna `aprobado` (por defecto `TRUE` para no afectar a los seeds ni a los usuarios creados por el admin), comprobación en el login, filtro en el buscador y bloque de pendientes en el panel. Rechazar borra la cuenta, ya que todavía no tiene historial.

**Motivo:**
Mejora propia sobre el enunciado para evitar cuentas falsas.

**Qué he aprendido:**

---

## 23. Separar "servicios que sé hacer" de "ofertas"
**Fecha:** 12/11/2026

**Prompt:**
> En el registro solo se eligen los servicios que el profesional puede ofertar; las ofertas (con tarifa y descripción) se crean después desde "Crear oferta". Propón cómo guardarlo en la base de datos sin alterar las tablas del enunciado, y actualiza el registro, el panel de pendientes y los seeds.

**Resultado:**
Nueva tabla `servicios_profesional` con clave primaria compuesta y claves foráneas en cascada. El registro ya no crea ofertas. Los seeds rellenan la tabla a partir de las ofertas existentes con un `INSERT ... SELECT`.

**Motivo:**
Evitar ofertas sin descripción ni tarifa propia en el buscador.

**Qué he aprendido:**

---

## 24. Envío de correos y generación de PDF (capa Service)
**Fecha:** 12/11/2026

**Prompt:**
> Siguiendo lo visto en clase, implementa una capa `App\Service` con `MailService`, `PdfService`, `MailOptions` (configuración SMTP como objeto) y `CreateMailer` (factoría que devuelve un `PHPMailer` configurado), usando PHPMailer ^6.8 y Dompdf. Las plantillas se renderizan con `extract` + `ob_start`. El correo lleva un banner incrustado con `addEmbeddedImage` y `cid`. Añade MailHog al `docker-compose.yml` (SMTP 1025, web 8025), con una configuración que también funcione en XAMPP. Úsalo para avisar al registrarse y al aprobar o rechazar una cuenta, adjuntando un PDF de bienvenida al aprobar.

**Resultado:**
- Las cuatro clases de servicio, tres plantillas de correo, una plantilla de PDF y MailHog en Docker.
- El fallo de un correo no bloquea la acción: se registra en el log.
- `extract` con `EXTR_SKIP` para que los datos no puedan pisar variables internas, y comprobación de que la plantilla existe antes de incluirla.
- MailHog no representa en su pestaña HTML los correos `multipart/related` (imágenes con `cid`). El correo es correcto y se comprueba en la pestaña "Source" o abriendo el `.eml` en un cliente de correo.

**Motivo:**
Requisito de la unidad: envío de correos con adjuntos y generación de PDF desde plantillas.

**Qué he aprendido:**
