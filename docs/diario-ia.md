# Diario de uso de IA — HoraXHora

Registro de las consultas hechas a la IA durante el desarrollo de **HoraXHora** (trueque de servicios). En cada entrada aparece el prompt que hice (redactado de forma clara), un resumen de lo que respondió la IA y mi conclusión sobre esa respuesta: si era correcta, qué tuve que cambiar y qué saqué en claro.

---

## 1. ¿Está bien diseñado mi diagrama E/R?
**Fecha:** 29/10/2026

**Prompt del alumno:**
> Te adjunto el diagrama Entidad-Relación de mi aplicación de trueque de servicios. Antes de programar nada quiero saber: ¿qué errores de diseño tiene (nombres, claves, tipos de datos, relaciones, datos duplicados) y por qué cada uno me daría problemas más adelante? Con el modelo ya corregido, ¿qué estructura de carpetas me recomiendas para PHP con MVC + DAO, Twig, JavaScript con `fetch` y Docker, y para qué sirve cada carpeta?

**Respuesta de la IA:**
- `catalogo` pasa a llamarse `categoria`.
- En `oferta servicio` sobraban campos copiados de `servicio` y faltaba un `UNIQUE (id_profesional, id_servicio)` para que nadie ofrezca dos veces el mismo servicio.
- El trueque debe apuntar a `id_oferta` en vez de copiar sus datos, y `valoraciones` debe ir enlazada con `id_solicitud` (1:1).
- Tipos mejor elegidos: `estado` como ENUM, confirmaciones como BOOLEAN, `telefono` como VARCHAR y `puntuacion` como TINYINT de 1 a 5.
- Estructura con `public/` como única carpeta accesible y capas Models / DAO / Controllers, vistas Twig y módulos JS.

**Conclusión:**
La respuesta fue muy útil porque no solo decía qué estaba mal, sino por qué. Lo del `telefono` como VARCHAR, por ejemplo, tiene sentido: un número que empieza por 0 o lleva `+34` se rompería como INT. Lo que más me sirvió es entender que no hay que copiar datos entre tablas, que se enlazan con la clave. Revisar la base de datos antes de programar me ahorró rehacer modelos y DAO después.

---

## 2. ¿Cómo adapto mi estructura a la del profesor?
**Fecha:** 29/10/2026

**Prompt del alumno:**
> El profesor nos ha dado esta estructura de carpetas en un .zip (`Dao`, `Views` dentro de `src`, `App.php`, `Autoloader.php`, `Router.php`). Ayúdame a encajar tu propuesta anterior en su plantilla respetando sus nombres: ¿qué carpetas y archivos le faltan para cumplir el enunciado y para qué sirve cada uno?

**Respuesta de la IA:**
- Esqueleto respetando la plantilla y añadiendo `database/` (esquema y datos de prueba), `docs/`, `public/assets/` (CSS y JS) y un `composer.json` con Twig.
- Aviso de una errata del enunciado: Composer no es un gestor de plantillas, es un gestor de dependencias.

**Conclusión:**
Buena respuesta: se adaptó a lo que pedía el profesor en vez de imponer su estructura. Lo de la errata de Composer está bien visto. Más adelante comprobé que la estructura del profesor no era obligatoria y borré `App.php` y `Autoloader.php` porque estaban vacíos (entrada 14), así que la IA fue algo rígida al respetarla tal cual, aunque al principio era lo más prudente.

---

## 3. ¿Cómo creo el repositorio sin tener conflictos?
**Fecha:** 29/10/2026

**Prompt del alumno:**
> Ya tengo el esqueleto del proyecto en local y quiero subirlo a GitHub desde el principio. ¿Cómo creo el repositorio (nombre, privado o público, si lo inicializo con README o no) para que el primer `push` no me dé conflictos? ¿Por qué pasa ese conflicto?

**Respuesta de la IA:**
- Repositorio `proyecto-noviembre` privado y vacío, con el primer commit "Estructura inicial del proyecto" en `main`.
- Si el repo se crea con README, GitHub ya tiene un commit que no está en local y el `push` se rechaza porque las historias no coinciden.

**Conclusión:**
Respuesta correcta y corta, justo lo que necesitaba. Entender el porqué del conflicto (dos historias distintas) me ayuda a no repetir el error. En el historial del repo se ve un "Initial commit" y un merge, así que en la práctica sí tuve que juntar historias una vez; la recomendación era buena, pero llegué un poco tarde.

---

## 4. ¿Cómo monto el entorno con Docker de la forma más sencilla?
**Fecha:** 30/10/2026 – 03/11/2026

**Prompt del alumno:**
> Ayúdame a montar el entorno Docker del proyecto de la forma más simple posible. Necesito PHP 8.2 + Apache con PDO MySQL, Xdebug (desde `xdebug.ini`), el DocumentRoot en `public/` y todas las rutas hacia `index.php`, y además MySQL 8 con el código montado como volumen, los `.sql` de `database/` cargados al crear la BD y las credenciales en variables de entorno. ¿Qué tiene que llevar el Dockerfile y qué el `docker-compose.yml`, cómo integro el `apache.conf` y el `xdebug.ini` del profesor y qué errores típicos me puedo encontrar?

**Respuesta de la IA:**
- Dockerfile con `php:8.2-apache`, `pdo_mysql`, Xdebug y `mod_rewrite`. Una primera versión más completa se simplificó porque `vendor/` entra por el volumen.
- `docker-compose.yml` con `app` (puerto 8080) y `db` (MySQL 8, volumen `db_data`). Los `.sql` van a `/docker-entrypoint-initdb.d` y las credenciales se leen con `getenv()`.
- Al integrarlo: declarar el volumen con nombre abajo del todo, cambiar el puerto de MySQL porque el 3306 estaba ocupado, y usar el `apache.conf` del profesor (`AllowOverride All`) para arreglar un 403.

**Conclusión:**
La primera versión era más complicada de lo necesario y tuve que pedir que la simplificara; la IA tiende a meter de más. Lo bueno es que con cada error (puerto ocupado, 403) explicaba la causa. Aprendí que los `.sql` solo se ejecutan la primera vez y que para recargarlos hace falta `down -v`, que borra los datos. El 17/11 tuve que corregir las rutas del `COPY` del Dockerfile porque apuntaban a una carpeta `Docker/` que no existe: la IA no comprobó dónde estaban realmente los archivos.

---

## 5. ¿Dónde van las plantillas Twig y los CSS, y por qué?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> En un proyecto PHP con MVC, ¿dónde deben ir las plantillas Twig y dónde los CSS? ¿Por qué unas van fuera de `public/` y otros dentro? Quiero las plantillas separadas en layout, partials y páginas, y los CSS enlazados desde la base de forma que funcionen en cualquier ruta y que cada página pueda añadir el suyo.

**Respuesta de la IA:**
- Plantillas en `templates/` (fuera de `public/`), divididas en `layout/`, `partials/` y `pages/`, con el `FilesystemLoader` apuntando a `../templates`.
- CSS en `public/assets/css/`, enlazados con rutas absolutas (`/assets/css/...`) y un `{% block styles %}` por página.
- Un partial no lleva `<!DOCTYPE>`, `<head>` ni `<body>`, y `public/` no aparece en la URL porque es la raíz web.

**Conclusión:**
Respuesta clara y correcta. La clave es quién tiene que leer cada archivo: las plantillas solo las lee PHP (fuera de `public/`) y el CSS lo descarga el navegador (dentro). Lo de las rutas que empiezan por `/` me evitó que los estilos se rompieran en rutas como `/admin/usuarios`.

---

## 6. ¿Cómo hago un header que cambie según el usuario?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> Ayúdame a plantear las plantillas `base.html.twig`, `header.html.twig` y `footer.html.twig` de HoraXHora. El header tiene que cambiar si hay sesión o no, mostrar el saldo de puntos, enseñar el enlace al panel solo al rol ADMIN y tener menú hamburguesa en móvil. ¿Qué bloques necesita la plantilla base y cómo hago para que el nombre de la app esté definido en un único sitio?

**Respuesta de la IA:**
- Layout base con bloques `title`, `styles`, `content` y `scripts`.
- Header con las variables globales `usuario`, `pagina_actual` y `app_nombre`, y el saldo con `id="saldo-puntos"` para actualizarlo por JS.
- El nombre **HoraXHora** definido una sola vez con `addGlobal`.

**Conclusión:**
Muy útil. Con `extends` y `block` todas las páginas comparten estructura y solo rellenan huecos. El `id` en el saldo fue una buena previsión para el requisito de actualizarlo sin recargar. Hay que tener en cuenta que ocultar el enlace del panel en el header no protege nada: la comprobación del rol ADMIN tiene que hacerse también en el servidor (como se hizo después en la entrada 22).

---

## 7. ¿Cómo pruebo las plantillas si todavía no tengo login?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> Ayúdame a entender cómo debe ser un `public/index.php` mínimo que cargue Composer, inicie sesión, configure Twig y enrute con un `switch` sobre la URL. ¿Cómo puedo simular un usuario logueado (normal y admin) para probar las plantillas antes de tener el login hecho?

**Respuesta de la IA:**
`index.php` con `session_start()`, Twig, variable global `usuario`, `switch` de rutas y un modo demo temporal (`?demo`, `?demo=admin`, `?salir`), más un `.htaccess` que manda todas las rutas a `index.php`.

**Conclusión:**
Solución práctica para no quedarme bloqueado esperando al login. El modo demo es peligroso si se queda en producción (cualquiera podría entrar como admin con `?demo=admin`), así que es algo que tengo que asegurarme de quitar. Al tener luego el login real y pasar el enrutado al `Router`, ya no tiene sentido mantenerlo.

---

## 8. ¿Cómo organizo los estilos de toda la web?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> Ayúdame a organizar la página de inicio (hero, cómo funciona, categorías y llamada a la acción) y los estilos de la web en `base.css`, `header.css`, `footer.css` e `inicio.css`. ¿Cuándo uso `em`, `rem` o `px`, y qué estilos deben ir en `base.css` para no repetirlos en cada página?

**Respuesta de la IA:**
`inicio.html.twig` con categorías de ejemplo (luego vendrán del DAO), `variables.css` con la paleta y `base.css` con reset, tipografía, footer pegado abajo con flex y botones `.btn` reutilizables.

**Conclusión:**
Cumplía el requisito de CSS3 nativo y adaptativo, y me quedó clara la diferencia entre `em`, `rem` y `px`. Pero la IA se pasó de nivel: metió variables CSS y cosas que no hemos visto en clase. Más adelante decidí que el CSS debía ser sencillo y a mi nivel, sin variables ni propiedades raras. En el repo actual ya no existe `variables.css`, y en la entrada 18 se vio que `base.css` usaba `var(--radio)` sin tenerla definida, consecuencia de ese cambio.

---

## 9. ¿Qué significan los avisos de SonarLint?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> SonarLint me marca avisos en el Dockerfile, en los CSS y en `index.php`. ¿Qué significa cada uno, es un error real o solo una recomendación, cómo lo corrijo y cómo configuro PhpStorm para que no se repitan al guardar?

**Respuesta de la IA:**
- Dockerfile: juntar los `RUN` en uno. El aviso de usuario root se puede marcar como seguro porque es entorno de desarrollo.
- CSS: meter `scroll-behavior: smooth` y las animaciones dentro de `prefers-reduced-motion` por accesibilidad.
- PHP: `require` → `require_once` y salto de línea al final, que se automatiza en *Settings → Editor → General → On Save*.

**Conclusión:**
Bien explicado: separó lo que había que arreglar de lo que se podía ignorar con motivo (el root en desarrollo). Lo de `prefers-reduced-motion` no lo conocía y es un tema de accesibilidad real. Preguntar "¿es un error o una recomendación?" fue la clave para no cambiar código a ciegas.

---

## 10. ¿Cómo creo la base de datos con datos de prueba coherentes?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> Ayúdame a pasar el modelo E/R y el script SQL del anexo del enunciado a MySQL, separado en `01_schema.sql` y `02_seeds.sql` para que Docker los ejecute en orden. ¿Qué restricciones debería añadir para que no entren datos imposibles, cómo hago unos datos de prueba coherentes (que los saldos cuadren con los trueques), qué son exactamente los seeds y cómo conecto PhpStorm a esta base de datos?

**Respuesta de la IA:**
- `01_schema.sql` con las 6 tablas, `CHECK` (saldo no negativo, horas y tarifas > 0, puntuación de 1 a 5) e índice por estado en las solicitudes.
- `02_seeds.sql` con 1 admin, 4 profesionales, categorías, servicios, ofertas (una desactivada), una solicitud de cada estado y valoraciones solo en las completadas. Contraseña de prueba con bcrypt.
- `SET NAMES utf8mb4` para los acentos.
- Aviso: el `ON DELETE CASCADE` del anexo borra el historial de trueques si se elimina un usuario.
- PhpStorm conecta con `localhost` y el puerto publicado; la app, desde dentro de Docker, con `db`.

**Conclusión:**
Una de las respuestas más completas. Que los saldos cuadren con los trueques hace que el profesor pueda probar la app sin ver datos raros. El aviso del `ON DELETE CASCADE` fue muy importante: por eso después, en el panel de admin, "dar de baja" desactiva al usuario en vez de borrarlo (entrada 22).

---

## 11. ¿Por qué usar un Singleton para la conexión PDO?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> Ayúdame a entender cómo crear la conexión a la base de datos con PDO usando el patrón Singleton dentro de la capa DAO, leyendo los datos de las variables de entorno de Docker y cargando las clases con el autoload de Composer. ¿Qué ventaja tiene el Singleton frente a crear una conexión en cada DAO? Si me pasas algo de código, pásamelo en el chat; no lo escribas en mis archivos.

**Respuesta de la IA:**
- `src/Dao/BD.php`: clase `final` con constructor privado y `getConexion()` estático que crea la conexión una sola vez, con `utf8mb4`, excepciones activadas y un mensaje genérico si falla.
- `composer.json` con autoload PSR-4 (`App\` → `src/`).
- Ruta temporal `/test-bd` para probarlo.

**Conclusión:**
El código era correcto, pero la IA escribió los archivos directamente en mi proyecto sin pedírselo. Le hice deshacerlo y lo copié yo a mano. Por eso en este prompt añado que me lo pase por el chat. Lección: tengo que controlar qué entra en mi proyecto y entender cada línea. Sobre el Singleton: una sola conexión compartida por todos los DAO, en vez de abrir una nueva en cada consulta.

---

## 12. ¿Qué diferencia hay entre un diagrama de arquitectura y uno de clases?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> Ayúdame a hacer un diagrama de la arquitectura MVC + DAO de mi proyecto con las carpetas reales, en capas apiladas (Vista, Controlador, Modelo), marcando qué está hecho y qué falta. ¿Esto me vale como el "diagrama de clases MVC" que pide el enunciado o es otra cosa?

**Respuesta de la IA:**
Diagrama en PNG y SVG: navegador arriba, capas Vista, Control y Modelo, y MySQL abajo, con lo pendiente en borde discontinuo. Aclaró que es un diagrama de arquitectura y no el de clases.

**Conclusión:**
Bien que me avisara de que no era lo que pide el enunciado, si no lo habría entregado mal. El diagrama de clases (atributos y métodos de cada modelo) lo tengo que hacer ahora que ya existen los modelos (entrada 26).

---

## 13. ¿Cómo hago un login seguro con lo visto en clase?
**Fecha:** 03/11/2026

**Prompt del alumno:**
> Ayúdame a hacer el login con el estilo de clase (formulario POST normal, `$_SESSION`, `header("Location")` y `session_destroy()` en el logout), adaptado a mi MVC + DAO con Twig y comprobando el usuario contra la base de datos. Las consultas del DAO tienen que usar `try/catch` con `PDOException`, `prepare()` cuando hay datos del usuario y `fetch(PDO::FETCH_ASSOC)`. ¿Por qué no puedo comparar la contraseña con `===` y qué datos debo guardar en la sesión?

**Respuesta de la IA:**
- `AuthController` con `login()` (GET muestra, POST procesa) y `logout()`, `UsuarioDao::buscarPorEmail()` y `login.html.twig`.
- Contraseña con `password_verify()`; en sesión solo id, nombre, rol y puntos.
- `prepare()` para evitar inyección SQL.

**Conclusión:**
La IA propuso antes versiones con `fetch` y token CSRF. Eran más avanzadas, pero no son lo que hemos dado en clase, así que las descarté. Es mejor algo que entiendo y puedo defender que algo "más pro" que no sé explicar. Lo importante que me llevo: la contraseña está hasheada, por eso se usa `password_verify()`, y nunca se guarda en la sesión.

---

## 14. ¿Por qué me sale `Class not found`?
**Fecha:** 04/11/2026

**Prompt del alumno:**
> ¿Me ayudas a revisar mi estructura sin cambiar nada y decirme si funciona? ¿Qué diferencia hay entre PSR-4, el autoload y el router? ¿Qué hace `declare(strict_types=1)`? ¿Necesito `Autoloader.php` si ya uso el de Composer? ¿Y por qué me sale `Class "App\Controllers\AuthController" not found`?

**Respuesta de la IA:**
- El error venía de que `AuthController.php` solo tenía el `namespace`, sin la clase, y de que `UsuarioDAO.php` no coincidía en mayúsculas con la clase `UsuarioDao` (en Linux no cargaría).
- PSR-4 es la norma namespace ↔ carpeta; Composer la aplica; el router decide qué ejecutar según la URL.
- `strict_types` se mantiene porque lo pide el enunciado.
- `App.php` y `Autoloader.php` sobran. Composer se añade al Dockerfile.

**Conclusión:**
Pedirle que analizara sin tocar nada fue buena idea: así entendí el problema en vez de que lo arreglara sin más. El aviso de las mayúsculas fue clave: en Mac funciona, pero en Docker (Linux) no. Aun así no lo arreglé en todos los archivos y el 17/11 tuve que renombrar todos los `*DAO.php` a `*Dao.php` (entrada 27).

---

## 15. ¿Cómo adapto mi enrutado al Router del profesor?
**Fecha:** 06/11/2026

**Prompt del alumno:**
> Este es el `index.php` y el `Router.php` de mi profesor. Ayúdame a adaptar mi enrutado a su forma de hacerlo (una clase con `dispatch()` y un `switch` por segmentos de la URL), con mis rutas actuales y en `src/Router.php`, sin complicarlo. ¿Cómo funciona eso de partir la URL en segmentos?

**Respuesta de la IA:**
`index.php` reducido a cargar el autoload, crear el `Router` y llamar a `dispatch()`. La clase arranca la sesión, prepara Twig, parte la URL con `explode` y decide con un `switch`, con `responderError()` para el 404. Se descartaron una versión con expresiones regulares y otra con todas las rutas futuras.

**Conclusión:**
La IA volvió a proponer cosas más complejas de lo necesario (regex, rutas que aún no existían) y tuve que frenarla. La versión final es como la del profesor y la entiendo entera: `explode('/', trim($uri, '/'))` y el primer trozo dice la página. Es mejor añadir un `case` cuando creo cada página.

---

## 16. ¿Cómo paso un diseño en SCSS a CSS normal?
**Fecha:** 06/11/2026

**Prompt del alumno:**
> Tengo este diseño de login escrito en SCSS y con estilos globales en el `body`. Ayúdame a pasarlo a CSS3 nativo con nomenclatura BEM sin tocar el fondo de la página. ¿Por qué los estilos en `body` o en `*` me pueden romper otras páginas? ¿Qué tipografía me recomiendas para HoraXHora y cómo la aplico a toda la web?

**Respuesta de la IA:**
- `auth.css` en CSS nativo con BEM (`auth`, `auth__input`, `auth__boton`...). Tras varias pruebas, tarjeta centrada con campos subrayados y botón redondeado.
- Tipografía Plus Jakarta Sans de Google Fonts, cargada en el `<head>` antes de los CSS.

**Conclusión:**
Útil para entender que SCSS no es CSS (tiene `$variables` y anidación) y que un estilo en el `body` dentro del CSS de una página afecta a toda la web. Hicieron falta varias versiones hasta que me gustó el diseño, así que la IA ayuda pero la decisión visual es mía. Si no veía los cambios era la caché: `Cmd + Shift + R`.

---

## 17. ¿Cómo hago un buscador que filtre sin recargar la página?
**Fecha:** 05/11/2026

**Prompt del alumno:**
> Ayúdame a plantear la página `/servicios` con un buscador de ofertas: un campo de texto (por servicio o por profesional), filtros por categoría y un selector para ordenar por valoración media o por precio, con los resultados pedidos por `fetch` a `GET /api/ofertas` y pintados con un `<template>` sin recargar. En el `OfertaDao`, ¿cómo monto una consulta con filtros opcionales usando `prepare()` sin concatenar los datos del usuario en el SQL? ¿Y cómo calculo la media de valoraciones en la misma consulta?

**Respuesta de la IA:**
- `servicios.html.twig` con el formulario, los botones de categoría y un `<template>` para cada tarjeta de oferta.
- `buscador.js`: monta la URL con `URLSearchParams` (que codifica los acentos), hace el `fetch` y crea una tarjeta por oferta clonando el `<template>`.
- `OfertaController::listarApi()` lee los filtros de `$_GET` y devuelve JSON.
- `OfertaDao::buscar()`: una consulta base con JOIN a servicios, categorías y usuarios, y LEFT JOIN a solicitudes y valoraciones para sacar `AVG` y `COUNT`. Cada filtro solo se añade si viene relleno, con un `?` y su valor en un array de parámetros que se pasa al `execute()`. El orden se elige con un `if` entre valores fijos.

**Conclusión:**
La forma de montar la consulta por partes me parece la idea más útil: el SQL se construye con texto fijo y los datos del usuario van siempre en los `?`, así no hay inyección. El `ORDER BY` no se puede poner con `?`, por eso se elige con un `if` entre opciones fijas y nunca se mete lo que llega por la URL. Usar LEFT JOIN es necesario para que las ofertas sin valoraciones también salgan. Un fallo que veo: la media se calcula por oferta (`GROUP BY o.id_oferta`), pero el enunciado pide ordenar por la valoración media del profesional, así que un profesional con varias ofertas podría salir con medias distintas. Tengo que revisarlo.

---

## 18. ¿Cómo hago el perfil del profesional?
**Fecha:** 10/11/2026

**Prompt del alumno:**
> Ayúdame a hacer la vista de perfil del profesional en Twig, extendiendo `layout/base.html.twig`. Tiene que mostrar los datos personales editables por formulario POST (patrón de clase: POST + `$_SESSION`), el saldo de puntos, la valoración media y las ofertas del usuario. ¿Qué tiene que hacer el `PerfilController`, qué consultas necesita el `PerfilDao` y cómo calculo la valoración media si las valoraciones no están enlazadas directamente con el usuario?

**Respuesta de la IA:**
Controlador, DAO, plantilla y CSS del perfil. La media se calcula con un JOIN entre `valoraciones`, `solicitudes_trueque` y `ofertas_profesionales`. Detectó que `base.css` usaba `var(--radio)` sin tenerla definida.

**Conclusión:**
El JOIN tiene sentido: la valoración va con la solicitud, la solicitud con la oferta y la oferta con el profesional, así que hay que recorrer ese camino. Al pegarlo tuve que corregir el namespace (`App\Dao`), la clave de sesión (`id`) y la ruta de la plantilla base, porque la IA no conocía exactamente mis nombres. Siempre hay que revisar que lo que genera encaje con mi código.

---

## 19. ¿Cómo convierto el registro en un modal reutilizable?
**Fecha:** 11/11/2026

**Prompt del alumno:**
> Partiendo del ejemplo de modal de clase (`Modal` con `prototype`, `cargarURL` y `cargarLogica`), ayúdame a convertir el registro en un modal. ¿Cómo completo los métodos vacíos del ejemplo, cómo reutilizo el mismo formulario en `/registro` y en el modal sin duplicar HTML, cómo sirvo el fragmento desde una ruta PHP y cómo consigo que el JS funcione aunque se cargue varias veces? ¿Qué validaciones tengo que repetir en PHP aunque ya estén en JS, y por qué?

**Respuesta de la IA:**
- Clase `Modal` completa y formulario en un partial de Twig usado por la página y por `/registro/formulario`.
- `registro.js` dentro de una función para evitar redeclaraciones y `?v=` en la URL para evitar la caché.
- Validación completa en PHP (contraseñas iguales, campos obligatorios).
- Detectó dos fallos del ejemplo original: `crearVelo()` no existía y el CSS tenía propiedades duplicadas.

**Conclusión:**
Muy buena respuesta: reutilizar el partial evita tener el formulario dos veces, y encontró fallos en el ejemplo de clase que yo no había visto. La idea más importante es que la validación de JS se puede saltar desde el navegador, así que PHP tiene que validar siempre.

---

## 20. ¿Cómo cargo los servicios según la categoría sin perder los marcados?
**Fecha:** 11/11/2026

**Prompt del alumno:**
> En el registro, el profesional elige una categoría y se cargan sus servicios con `fetch` desde `GET /api/servicios?categoria=`. Ayúdame a conseguir que pueda marcar servicios de varias categorías sin perder los que ya eligió y que los marcados se vean al final de la lista, con un diseño en dos columnas y chips en vez de casillas. ¿Cómo guardo en JS los elegidos para que no se pierdan al cambiar de categoría y cómo valido en PHP que los servicios existen?

**Respuesta de la IA:**
- Endpoint en `CatalogoController` y métodos de catálogo en `ServicioDao`.
- El JS guarda los elegidos en un objeto y repinta la lista en cada cambio.
- Formulario en dos columnas con CSS Grid y chips accesibles con `:has(input:checked)`.

**Conclusión:**
La lógica de guardar los elegidos en un objeto aparte de lo que se ve en pantalla funciona bien y la entiendo. Pero `:has()` es CSS que no hemos dado y no lo sabría defender, así que esa parte la tendría que cambiar por algo más sencillo (por ejemplo, una clase que se añade desde JS al marcar).

---

## 21. ¿Cómo meto imágenes en la landing sin romper el diseño?
**Fecha:** 11/11/2026

**Prompt del alumno:**
> Ayúdame a meter cuatro imágenes en `inicio.html.twig` sin romper el diseño: una en el hero (dos columnas en escritorio, apilado en móvil) y una sección nueva con las otras tres en grid. También quiero que los botones de "Únete" abran el modal de registro. ¿Qué gano con `loading="lazy"` y con un buen `alt`, y en qué imágenes debo usar cada cosa?

**Respuesta de la IA:**
Hero en grid y sección "Profesionales como tú" con `figure`/`figcaption`, `aspect-ratio` y `object-fit`. Corrigió también que faltaba `transition` en el hover de los pasos y que el grid de categorías no tenía columnas.

**Conclusión:**
Las imágenes quedaron bien y aprendí que `lazy` hace que no se descarguen hasta que hacen falta y que el `alt` es para lectores de pantalla y por si la imagen no carga. Lo que no me convence es que la IA metiera `transition` y otras propiedades que me había propuesto no usar: tengo que repasar `inicio.css` para dejarlo a mi nivel.

---

## 22. ¿Cómo diseño el panel de administración?
**Fecha:** 12/11/2026

**Prompt del alumno:**
> Ayúdame a diseñar el panel de administración como una única página `/admin` con pestañas que cargan su contenido por `fetch` sin recargar, usando una API REST bajo `/api/admin/...` con los métodos HTTP adecuados (GET, POST, PUT, PATCH, DELETE) y comprobando siempre en el servidor que el rol es ADMIN. Secciones: categorías, servicios con filtro, usuarios, historial de trueques con cancelación y moderación de reseñas. ¿Qué hago con los usuarios que se dan de baja para no perder el historial?

**Respuesta de la IA:**
`AdminController`, cuatro DAO nuevos (`CategoriaDao`, `TruequeDao`, `ValoracionDao`, `UsuarioAdminDao`), `admin.js` y `admin.css`. Decisiones:
- "Dar de baja" desactiva al usuario (columna `activo`) en vez de borrarlo, por el `ON DELETE CASCADE`.
- El admin no puede darse de baja ni borrarse a sí mismo.
- Solo se cancelan trueques no terminados, comprobándolo en el propio `UPDATE`.
- La moderación permite quitar solo el comentario o borrar la reseña entera.

**Conclusión:**
Las decisiones de lógica son muy buenas (sobre todo la de desactivar en vez de borrar y la de que el admin no pueda borrarse a sí mismo). El problema es el tamaño: casi 1000 líneas de `admin.js` y 500 de controlador, mucho más de lo que puedo explicar en una defensa. Por eso decidí rehacer el panel en PHP con formularios y Twig, que es lo que hemos visto en clase, y mantener solo las ideas de diseño.

---

## 23. ¿Cómo evito cuentas falsas?
**Fecha:** 12/11/2026

**Prompt del alumno:**
> Quiero que los registros nuevos queden pendientes hasta que el administrador los acepte o rechace: el usuario pendiente no puede iniciar sesión, sus ofertas no salen en el buscador y en el panel hay un contador y una lista de pendientes con los servicios que eligió cada uno. Ayúdame a plantearlo: ¿qué tengo que cambiar en la base de datos y dónde hago las comprobaciones? ¿Cómo lo añado sin romper los usuarios que ya existen en los seeds?

**Respuesta de la IA:**
Columna `aprobado` con `TRUE` por defecto (para no afectar a los seeds ni a los usuarios creados por el admin), comprobación en el login, filtro en el buscador y bloque de pendientes en el panel. Rechazar borra la cuenta porque aún no tiene historial.

**Conclusión:**
Mejora propia que no pedía el enunciado y que tiene sentido en una app real. El valor por defecto `TRUE` es un buen truco para añadir una columna sin tener que tocar todos los datos anteriores. Borrar al rechazar está bien porque, al no tener trueques, no se pierde nada.

---

## 24. ¿Cómo separo "lo que sé hacer" de "lo que oferto"?
**Fecha:** 12/11/2026

**Prompt del alumno:**
> En el registro solo se eligen los servicios que el profesional sabe hacer; las ofertas (con tarifa y descripción) se crean después desde "Crear oferta". Ayúdame a pensar cómo guardarlo en la base de datos sin modificar las tablas del enunciado, y qué tengo que cambiar en el registro, en el panel de pendientes y en los seeds.

**Respuesta de la IA:**
Tabla nueva `servicios_profesional` con clave primaria compuesta y claves foráneas en cascada. El registro ya no crea ofertas. Los seeds rellenan la tabla con un `INSERT ... SELECT` desde las ofertas existentes.

**Conclusión:**
Me gusta porque no toca las tablas del enunciado y soluciona un problema real: antes salían ofertas sin descripción ni tarifa en el buscador. La clave primaria compuesta impide que un profesional tenga el mismo servicio dos veces. El `INSERT ... SELECT` no lo conocía: sirve para rellenar una tabla con datos que ya están en otra.

---

## 25. ¿Cómo envío correos con PDF adjunto?
**Fecha:** 12/11/2026

**Prompt del alumno:**
> Siguiendo lo visto en clase, ayúdame a montar una capa `App\Service` con `MailService`, `PdfService`, `MailOptions` (configuración SMTP como objeto) y `CreateMailer` (factoría que devuelve un `PHPMailer` configurado), con PHPMailer ^6.8 y Dompdf. Las plantillas se renderizan con `extract` + `ob_start`, el correo lleva un banner con `addEmbeddedImage` y `cid`, y uso MailHog en Docker (SMTP 1025, web 8025) para probarlo. Quiero avisar al registrarse y al aprobar o rechazar una cuenta, con un PDF de bienvenida al aprobar. ¿Cómo encaja cada clase y qué pasa si el correo falla?

**Respuesta de la IA:**
- Cuatro clases de servicio, tres plantillas de correo, una de PDF y MailHog en Docker.
- Si un correo falla, la acción no se bloquea: se guarda en el log.
- `extract` con `EXTR_SKIP` para que los datos no pisen variables internas.
- MailHog no enseña en su pestaña HTML los correos con imágenes `cid`; se comprueba en "Source".

**Conclusión:**
Siguió la estructura de clase, que era lo que quería. Lo de que el fallo del correo no bloquee la aprobación es una buena decisión: el usuario no debe quedarse sin aprobar porque el servidor de correo falle. El aviso de MailHog me ahorró pensar que el banner no funcionaba cuando el correo estaba bien.

---

## 26. ¿Cómo creo los modelos a partir de las tablas?
**Fecha:** 16/11/2026

**Prompt del alumno:**
> Ayúdame a crear los modelos de mi proyecto (`Categoria`, `Servicio`, `OfertaProfesional`, `SolicitudTrueque`, `Valoracion` y `Usuarios`) a partir de las tablas de la base de datos, con tipado estricto, atributos `protected`, constructor, getters y un método estático `desdeArray()` que cree el objeto a partir de una fila de PDO. ¿Por qué convertir las filas en objetos en vez de usar los arrays directamente?

**Respuesta de la IA:**
Un modelo por tabla en `src/Models/` con sus atributos tipados, constructor, getters y `desdeArray()`, que pasa los nombres de columna de la BD (`id_servicio`, `puntos_hora_referencia`) a atributos en camelCase y convierte los números con `(int)`.

**Conclusión:**
Con los modelos el código es más claro y PHP avisa si un dato no es del tipo correcto, que es lo que pide el `strict_types`. Los `(int)` son necesarios porque PDO devuelve los números como texto. Ojo: todos los modelos extienden de una clase `Modelo` que no está en el repositorio, así que al usarlos daría `Class not found`; tengo que crearla o quitar el `extends`. Con los modelos hechos ya puedo hacer el diagrama de clases que quedó pendiente en la entrada 12.

---

## 27. ¿Por qué en Docker falla lo que en mi Mac funciona?
**Fecha:** 17/11/2026

**Prompt del alumno:**
> En mi Mac la aplicación funciona, pero en Docker me falla al cargar los DAO y al construir la imagen. ¿Me ayudas a entender por qué puede pasar esto si el código es el mismo? ¿Dónde tengo que mirar: los nombres de los archivos frente a los de las clases, las rutas del Dockerfile...?

**Respuesta de la IA:**
- macOS no distingue mayúsculas en los nombres de archivo y Linux sí. Los archivos `CategoriaDAO.php`, `OfertaDAO.php`, `PerfilDAO.php`, etc. no coincidían con las clases `CategoriaDao`, `OfertaDao`... y el autoload no los encontraba. Se renombran todos a `*Dao.php` y se actualizan los `use` del `PerfilController`.
- En el Dockerfile, los `COPY` apuntaban a `Docker/xdebug.ini` y `Docker/apache.conf`, pero esos archivos están en la raíz del proyecto.

**Conclusión:**
Es el mismo problema que me avisó la IA en la entrada 14 y que no arreglé del todo. Ahora lo tengo claro: el nombre del archivo tiene que ser exactamente igual que el de la clase, con mayúsculas incluidas, porque el proyecto se ejecuta en Linux. Y lo de las rutas del `COPY` demuestra que la IA a veces da rutas sin comprobar dónde están de verdad los archivos, así que hay que revisarlas.

---

## 28. ¿Cómo paso el panel de administración a PHP con formularios?
**Fecha:** 26/11/2026

**Prompt del alumno:**
> El panel de administración que hice con `fetch` y una API REST (casi 1000 líneas de `admin.js`) es demasiado grande para defenderlo. Ayúdame a rehacerlo con lo visto en clase: cada sección es una página Twig (`/admin/categorias`, `/admin/usuarios`...), las acciones van por formularios POST a `/admin/{sección}/{acción}/{id}` y el `AdminController` comprueba el rol ADMIN en cada petición, manteniendo las decisiones de antes (desactivar en vez de borrar, el admin no se borra a sí mismo, solo se cancelan trueques sin terminar). ¿Cómo enseño un mensaje de "guardado" o de error si después del POST redirijo a otra página?

**Respuesta de la IA:**
- `admin.js` eliminado. El `Router` reparte con `despacharAdmin()`: si la petición es POST llama a `accion()` y si es GET a `panel()`.
- `AdminController` con `comprobarAdmin()` al principio de cada método y un método privado por sección (`accionCategorias`, `accionUsuarios`...).
- Las acciones que cambian datos solo se aceptan por POST, para que un enlace GET no pueda borrar nada.
- Después de cada POST se redirige con `header('Location: /admin/...')`, para que F5 no repita la acción. El mensaje se guarda antes en `$_SESSION['admin_mensaje']` y se enseña en la página siguiente.

**Conclusión:**
Ha sido la mejor decisión del proyecto: el panel hace lo mismo, pero ahora lo entiendo entero y está hecho con lo que hemos visto en clase. La idea de redirigir siempre después de un POST la aplico a todo; antes, si recargaba la página después de borrar algo, el navegador volvía a mandar el formulario. Guardar el mensaje en la sesión es la forma de que sobreviva a la redirección. Lo de que las acciones solo vayan por POST también es seguridad: una acción por GET se podría lanzar solo con que alguien hiciera clic en un enlace.

---

## 29. ¿Cómo dejo crear ofertas sin que se pueda trucar el precio?
**Fecha:** 26/11/2026

**Prompt del alumno:**
> Ayúdame a hacer la página `/ofertas/crear`: el profesional elige uno de los servicios que marcó en el registro y que todavía no tenga ofertado, le pone una tarifa y una descripción y lo publica con `fetch` a `POST /api/ofertas`, y debajo se ven sus ofertas actuales. ¿Qué tengo que comprobar en el servidor para que nadie pueda ofertar un servicio que no es suyo o poner un precio absurdo cambiando el JS desde el navegador?

**Respuesta de la IA:**
- `crearoferta.html.twig`, `crear_oferta.js` y dos endpoints: `GET /api/mis-ofertas` (lo que puedo ofertar y lo que ya oferto) y `POST /api/ofertas`.
- En `crearApi()`: el profesional sale de la sesión y no del JSON; el servicio tiene que estar entre los suyos sin ofertar; el precio tiene que estar entre la mitad y vez y media del de referencia (si la referencia es 10, de 5 a 15); y la descripción tiene que tener entre 10 y 500 caracteres.
- El JS pone por defecto el precio de referencia y los márgenes en el `min` y el `max` del input.

**Conclusión:**
Me ha quedado claro que los límites del input (`min`, `max`) son solo para que sea más cómodo usarlo, pero no protegen nada: cualquiera los cambia con el inspector. Por eso el servidor vuelve a calcular los márgenes con el precio de la base de datos. El límite del 50 %–150 % es una decisión mía para que nadie cobre 1 punto ni 500. Un fallo: la descripción se mide con `strlen()`, que cuenta bytes, y las letras con tilde o la ñ ocupan 2. Tendría que usar `mb_strlen()`.

---

## 30. ¿Cómo solicito un servicio desde el buscador?
**Fecha:** 26/11/2026

**Prompt del alumno:**
> Ayúdame a añadir en el buscador un botón "Solicitar" en cada oferta que abra un modal para elegir las horas, la fecha y la hora, y que lo mande con `fetch` a `POST /api/solicitudes`. ¿Qué validaciones tiene que hacer PHP con las horas, la fecha y los puntos? ¿Cómo evito que alguien se pida un servicio a sí mismo o que cambie lo que cuesta?

**Respuesta de la IA:**
`SolicitudController::crearApi()` valida en orden:
1. Horas entre 0,5 y 8, de media en media hora (`fmod($horas * 2, 1)`).
2. Fecha real y futura: `DateTime::createFromFormat()` y comparar con `format()`, porque acepta fechas como el 31 de febrero.
3. Que la oferta exista y esté activa.
4. Que no sea tu propia oferta.
5. Los puntos se calculan en el servidor (`puntos_hora × horas`) y se comprueba que el saldo llega, aunque los puntos no se quitan hasta que se complete.

El cliente siempre sale de `$_SESSION` y la solicitud se guarda en estado `SOLICITADO`.

**Conclusión:**
Lo que más me ha servido es lo de `createFromFormat()`: no sabía que PHP convierte el 31/02 en el 3 de marzo sin avisar, y comparando con `format()` se detecta. El precio lo calcula siempre el servidor con el dato de la base de datos, igual que en "Crear oferta": si lo mandara el JS, cualquiera podría pedir 8 horas por 1 punto. Comprobar el saldo al solicitar está bien, pero como los puntos no se reservan, alguien podría hacer varias solicitudes que juntas superen su saldo. Lo tengo que volver a comprobar al completar el trueque.

---

## 31. ¿Cómo hago la agenda con calendario y acciones seguras?
**Fecha:** 26/11/2026

**Prompt del alumno:**
> Ayúdame a plantear la página `/agenda`: un calendario con vista de mes y de semana con los trueques que me han pedido y los que he pedido yo, un bloque con las solicitudes pendientes de responder y un modal con el detalle de cada trueque. Los datos vienen de `GET /api/agenda` y las acciones van a `POST /api/agenda/{aceptar|rechazar|cancelar}/{id}`. ¿Cómo me aseguro en la base de datos de que nadie acepta o cancela un trueque que no es suyo cambiando el id de la URL?

**Respuesta de la IA:**
- `AgendaController` con la página, `listarApi()` (recibidas y enviadas del usuario de la sesión) y `accionApi()`.
- `TruequeDao` con `recibidas()`, `enviadas()`, `aceptar()`, `rechazar()` y `cancelarDeCliente()`. Cada `UPDATE` comprueba en el `WHERE` el estado actual y que el trueque es de quien lo pide (para aceptar, que la oferta sea suya con un `IN (SELECT ...)`). Con `rowCount()` se sabe si ha cambiado algo.
- `agenda.js` pinta el mes o la semana con funciones de fechas (`lunesDe`, `sumarDias`, `esHoy`), flechas ↓/↑ para "me lo piden" y "lo he pedido", y colores según el estado.

**Conclusión:**
Meter la comprobación en el propio `UPDATE` es más seguro que buscar primero el trueque y luego actualizarlo: se hace todo de una vez y si no es tuyo, simplemente no cambia ninguna fila. Es la misma idea de la cancelación en el panel de admin. La parte del calendario en JS es larga (más de 400 líneas) y es lo que más me costaría explicar, así que me la tengo que repasar. Todavía falta la parte más importante del enunciado: completar el trueque con la doble confirmación y pasar los puntos, y después la valoración.

---

## 32. ¿Cómo dejo el CSS a mi nivel?
**Fecha:** 26/11/2026

**Prompt del alumno:**
> Ayúdame a revisar mis CSS y simplificarlos a lo que hemos visto en clase, sin variables (`var()`), `:has()`, `clamp()`, `color-mix()`, `transform` ni `transition`, y con los colores puestos directamente. Quiero que se vea igual o muy parecido y que siga siendo responsive. ¿Qué pierdo al quitar las variables?

**Respuesta de la IA:**
Todos los CSS reescritos sin variables ni propiedades avanzadas y con los colores escritos directamente. Los chips del registro dejan de usar `:has()`. Se mantiene el diseño responsive con media queries y Grid/Flexbox.

**Conclusión:**
Ahora entiendo cada línea del CSS, que es lo que necesito para la defensa. Lo que pierdo es que, si quiero cambiar un color, tengo que cambiarlo en todos los archivos donde sale y no en un único sitio. Para un proyecto de este tamaño compensa. Solo queda `text-transform: uppercase`, que es una propiedad de texto normal y no tiene nada que ver con `transform`. En el mismo commit renombré la carpeta `service` a `Service` para que coincida con el namespace `App\Service`: es otra vez el problema de las mayúsculas de la entrada 27.
