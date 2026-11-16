// * Panel de administración: pestañas que cambian el contenido sin recargar la página
document.addEventListener("DOMContentLoaded", function () {
    let contenido = document.getElementById("admin-contenido");
    let pestanas = document.querySelectorAll(".admin__pestana");

    // * Qué función monta cada sección
    let secciones = {
        categorias: iniciarCategorias,
        servicios: iniciarServicios,
        usuarios: iniciarUsuarios,
        trueques: iniciarTrueques,
        resenas: iniciarResenas
    };

    function abrirSeccion(nombre) {
        // * Marcamos la pestaña activa
        for (let pestana of pestanas) {
            let activa = pestana.dataset.seccion == nombre;
            pestana.classList.toggle("is-active", activa);
            pestana.setAttribute("aria-selected", activa);
        }

        // * Copiamos el <template> de la sección dentro del contenido
        let plantilla = document.getElementById("tpl-" + nombre);
        if (plantilla == null) {
            plantilla = document.getElementById("tpl-proximamente");
        }

        contenido.innerHTML = "";
        contenido.appendChild(plantilla.content.cloneNode(true));

        if (secciones[nombre]) {
            secciones[nombre]();
        }
    }

    for (let pestana of pestanas) {
        pestana.onclick = function () {
            abrirSeccion(pestana.dataset.seccion);
        };
    }

    // ! Si hay registros pendientes, abrimos directamente Usuarios para que el admin los vea
    let badge = document.getElementById("badge-pendientes");
    if (badge && !badge.hidden) {
        abrirSeccion("usuarios");
    } else {
        abrirSeccion("categorias");
    }
});

// * ===== Funciones de ayuda (las usan todas las secciones) =====

// * Hace una petición a la API y devuelve el JSON. Si falla la conexión devuelve un error
async function pedirApi(url, metodo, datos) {
    let opciones = {method: metodo || "GET", headers: {}};

    if (datos) {
        opciones.headers["Content-Type"] = "application/json";
        opciones.body = JSON.stringify(datos);
    }

    try {
        let respuesta = await fetch(url, opciones);
        return await respuesta.json();
    } catch (error) {
        return {ok: false, error: "Error de conexión con el servidor"};
    }
}

// * Crea un elemento con su clase y su texto en una sola línea
function crearElemento(etiqueta, clase, texto) {
    let elemento = document.createElement(etiqueta);
    if (clase) {
        elemento.className = clase;
    }
    if (texto !== undefined) {
        elemento.textContent = texto;
    }
    return elemento;
}

// * Crea una celda <td> con texto
function crearCelda(texto) {
    return crearElemento("td", "", texto);
}

// * Crea una celda con una "pastilla" de color (rol, estado...)
function crearCeldaInsignia(texto, clase) {
    let td = crearElemento("td");
    td.appendChild(crearElemento("span", "admin__insignia " + clase, texto));
    return td;
}

// * Crea un botón pequeño
function crearBoton(texto, clase) {
    let boton = crearElemento("button", clase, texto);
    boton.type = "button";
    return boton;
}

// * Pone un mensaje de ok (verde) o de error (rojo)
function mostrarMensaje(elemento, texto, esError) {
    elemento.textContent = texto;
    elemento.className = esError ? "admin__mensaje admin__mensaje--error" : "admin__mensaje";
    elemento.hidden = false;
}

// * Fila de "no hay nada" que ocupa toda la tabla
function filaVacia(texto, columnas) {
    let fila = crearElemento("tr");
    let celda = crearCelda(texto);
    celda.colSpan = columnas;
    fila.appendChild(celda);
    return fila;
}

// * Rellena un <select> con las categorías. textoPrimero es la primera opción (vacía)
function rellenarSelectCategorias(select, categorias, textoPrimero) {
    select.innerHTML = "";

    let primera = crearElemento("option", "", textoPrimero);
    primera.value = "";
    select.appendChild(primera);

    for (let categoria of categorias) {
        let opcion = crearElemento("option", "", categoria.nombre);
        opcion.value = categoria.id_categoria;
        select.appendChild(opcion);
    }
}

// * "2026-10-10 10:00:00" (formato MySQL) -> "10/10/26, 10:00"
function formatearFecha(textoFecha) {
    // ? El replace cambia el espacio por una T para que JavaScript entienda la fecha
    let fecha = new Date(textoFecha.replace(" ", "T"));
    return fecha.toLocaleString("es-ES", {dateStyle: "short", timeStyle: "short"});
}

// * ===== Sección: categorías =====
function iniciarCategorias() {
    let form = document.getElementById("form-categoria");
    let filas = document.getElementById("cat-filas");
    let mensaje = document.getElementById("cat-mensaje");
    let botonGuardar = document.getElementById("cat-guardar");
    let botonCancelar = document.getElementById("cat-cancelar");

    // * El mismo formulario sirve para crear y para editar
    function modoCrear() {
        form.reset();
        form["id"].value = "";
        botonGuardar.textContent = "Añadir categoría";
        botonCancelar.hidden = true;
    }

    function modoEditar(categoria) {
        form["id"].value = categoria.id_categoria;
        form["nombre"].value = categoria.nombre;
        form["descripcion"].value = categoria.descripcion || "";
        botonGuardar.textContent = "Guardar cambios";
        botonCancelar.hidden = false;
        form["nombre"].focus();
    }

    async function pintarTabla() {
        let resultado = await pedirApi("/api/admin/categorias");
        filas.innerHTML = "";

        if (!resultado.ok) {
            mostrarMensaje(mensaje, resultado.error, true);
            return;
        }

        if (resultado.categorias.length == 0) {
            filas.appendChild(filaVacia("Todavía no hay categorías.", 4));
            return;
        }

        for (let categoria of resultado.categorias) {
            let fila = crearElemento("tr");
            fila.appendChild(crearCelda(categoria.nombre));
            fila.appendChild(crearCelda(categoria.descripcion || "—"));
            fila.appendChild(crearCelda(categoria.num_servicios));

            let acciones = crearElemento("td", "admin__acciones");

            let botonEditar = crearBoton("Editar", "admin__boton");
            botonEditar.onclick = function () {
                modoEditar(categoria);
            };

            let botonBorrar = crearBoton("Borrar", "admin__boton admin__boton--peligro");
            botonBorrar.onclick = async function () {
                // ! Avisamos de que se borra todo lo que cuelga de la categoría
                let texto = '¿Borrar la categoría "' + categoria.nombre + '"?';
                if (categoria.num_servicios > 0) {
                    texto += "\n\nOjo: también se borrarán sus " + categoria.num_servicios +
                        " servicios y todas las ofertas de esos servicios.";
                }

                if (!confirm(texto)) {
                    return;
                }

                let respuesta = await pedirApi("/api/admin/categorias/" + categoria.id_categoria, "DELETE");

                if (respuesta.ok) {
                    mostrarMensaje(mensaje, "Categoría borrada", false);
                    modoCrear();
                    pintarTabla();
                } else {
                    mostrarMensaje(mensaje, respuesta.error, true);
                }
            };

            acciones.appendChild(botonEditar);
            acciones.appendChild(botonBorrar);
            fila.appendChild(acciones);
            filas.appendChild(fila);
        }
    }

    form.onsubmit = async function (ev) {
        ev.preventDefault();

        let datos = {
            nombre: form["nombre"].value.trim(),
            descripcion: form["descripcion"].value.trim()
        };

        if (datos.nombre == "") {
            mostrarMensaje(mensaje, "El nombre es obligatorio", true);
            return;
        }

        // * Si hay id estamos editando (PUT), si no, creando (POST)
        let id = form["id"].value;
        let respuesta;

        if (id == "") {
            respuesta = await pedirApi("/api/admin/categorias", "POST", datos);
        } else {
            respuesta = await pedirApi("/api/admin/categorias/" + id, "PUT", datos);
        }

        if (respuesta.ok) {
            mostrarMensaje(mensaje, id == "" ? "Categoría creada" : "Categoría actualizada", false);
            modoCrear();
            pintarTabla();
        } else {
            mostrarMensaje(mensaje, respuesta.error, true);
        }
    };

    botonCancelar.onclick = modoCrear;

    pintarTabla();
}

// * ===== Sección: servicios =====
async function iniciarServicios() {
    let form = document.getElementById("form-servicio");
    let filtro = document.getElementById("serv-filtro");
    let filas = document.getElementById("serv-filas");
    let mensaje = document.getElementById("serv-mensaje");
    let botonGuardar = document.getElementById("serv-guardar");
    let botonCancelar = document.getElementById("serv-cancelar");

    // * El mismo formulario sirve para crear y para editar
    function modoCrear() {
        form.reset();
        form["id"].value = "";
        botonGuardar.textContent = "Añadir servicio";
        botonCancelar.hidden = true;
    }

    function modoEditar(servicio) {
        form["id"].value = servicio.id_servicio;
        form["categoria"].value = servicio.id_categoria;
        form["nombre"].value = servicio.nombre;
        form["puntos"].value = servicio.puntos_hora_referencia;
        form["descripcion"].value = servicio.descripcion || "";
        botonGuardar.textContent = "Guardar cambios";
        botonCancelar.hidden = false;
        form["nombre"].focus();
    }

    async function pintarTabla() {
        let url = "/api/admin/servicios";
        if (filtro.value != "") {
            url = url + "?categoria=" + filtro.value;
        }

        let resultado = await pedirApi(url);
        filas.innerHTML = "";

        if (!resultado.ok) {
            mostrarMensaje(mensaje, resultado.error, true);
            return;
        }

        if (resultado.servicios.length == 0) {
            filas.appendChild(filaVacia("No hay servicios en esta categoría.", 5));
            return;
        }

        for (let servicio of resultado.servicios) {
            let fila = crearElemento("tr");
            fila.appendChild(crearCelda(servicio.nombre));
            fila.appendChild(crearCelda(servicio.categoria));
            fila.appendChild(crearCelda(servicio.puntos_hora_referencia));
            fila.appendChild(crearCelda(servicio.num_ofertas));

            let acciones = crearElemento("td", "admin__acciones");

            let botonEditar = crearBoton("Editar", "admin__boton");
            botonEditar.onclick = function () {
                modoEditar(servicio);
            };

            let botonBorrar = crearBoton("Borrar", "admin__boton admin__boton--peligro");
            botonBorrar.onclick = async function () {
                // ! Si hay profesionales ofreciendo este servicio, perderán su oferta
                let texto = '¿Borrar el servicio "' + servicio.nombre + '"?';
                if (servicio.num_ofertas > 0) {
                    texto += "\n\nOjo: " + servicio.num_ofertas + " profesional(es) lo ofrecen. " +
                        "Se borrarán sus ofertas, solicitudes y valoraciones.";
                }

                if (!confirm(texto)) {
                    return;
                }

                let respuesta = await pedirApi("/api/admin/servicios/" + servicio.id_servicio, "DELETE");

                if (respuesta.ok) {
                    mostrarMensaje(mensaje, "Servicio borrado", false);
                    modoCrear();
                    pintarTabla();
                } else {
                    mostrarMensaje(mensaje, respuesta.error, true);
                }
            };

            acciones.appendChild(botonEditar);
            acciones.appendChild(botonBorrar);
            fila.appendChild(acciones);
            filas.appendChild(fila);
        }
    }

    form.onsubmit = async function (ev) {
        ev.preventDefault();

        let datos = {
            id_categoria: Number(form["categoria"].value),
            nombre: form["nombre"].value.trim(),
            descripcion: form["descripcion"].value.trim(),
            puntos: Number(form["puntos"].value)
        };

        // * Comprobaciones rápidas (el PHP las vuelve a hacer)
        if (datos.id_categoria == 0) {
            mostrarMensaje(mensaje, "Elige una categoría", true);
            return;
        }
        if (datos.nombre == "") {
            mostrarMensaje(mensaje, "El nombre es obligatorio", true);
            return;
        }
        if (datos.puntos < 1 || datos.puntos > 1000) {
            mostrarMensaje(mensaje, "Los puntos por hora deben estar entre 1 y 1000", true);
            return;
        }

        // * Si hay id estamos editando (PUT), si no, creando (POST)
        let id = form["id"].value;
        let respuesta;

        if (id == "") {
            respuesta = await pedirApi("/api/admin/servicios", "POST", datos);
        } else {
            respuesta = await pedirApi("/api/admin/servicios/" + id, "PUT", datos);
        }

        if (respuesta.ok) {
            mostrarMensaje(mensaje, id == "" ? "Servicio creado" : "Servicio actualizado", false);
            modoCrear();
            pintarTabla();
        } else {
            mostrarMensaje(mensaje, respuesta.error, true);
        }
    };

    botonCancelar.onclick = modoCrear;
    filtro.onchange = pintarTabla;

    // ! Primero cargamos las categorías (para los dos select) y después la tabla
    let resultado = await pedirApi("/api/admin/categorias");
    if (resultado.ok) {
        rellenarSelectCategorias(form["categoria"], resultado.categorias, "-- Elige categoría --");
        rellenarSelectCategorias(filtro, resultado.categorias, "Todas las categorías");
    } else {
        mostrarMensaje(mensaje, resultado.error, true);
    }

    pintarTabla();
}

// * ===== Sección: usuarios =====
function iniciarUsuarios() {
    let form = document.getElementById("form-usuario");
    let buscador = document.getElementById("usu-buscador");
    let filas = document.getElementById("usu-filas");
    let mensaje = document.getElementById("usu-mensaje");
    let botonGuardar = document.getElementById("usu-guardar");
    let botonCancelar = document.getElementById("usu-cancelar");
    let campoPassword = document.getElementById("usu-campo-password");
    let cajaPendientes = document.getElementById("usu-pendientes");
    let listaPendientes = document.getElementById("usu-pendientes-lista");
    let numPendientes = document.getElementById("usu-pendientes-num");

    // * El mismo formulario sirve para crear y para editar
    function modoCrear() {
        form.reset();
        form["id"].value = "";
        campoPassword.hidden = false;
        botonGuardar.textContent = "Añadir usuario";
        botonCancelar.hidden = true;
    }

    function modoEditar(usuario) {
        form["id"].value = usuario.id_usuario;
        form["nombre"].value = usuario.nombre;
        form["apellidos"].value = usuario.apellidos;
        form["email"].value = usuario.email;
        form["telefono"].value = usuario.telefono || "";
        form["puntos"].value = usuario.puntos_saldo;
        // ! Al editar no se cambia la contraseña desde aquí
        campoPassword.hidden = true;
        botonGuardar.textContent = "Guardar cambios";
        botonCancelar.hidden = false;
        form["nombre"].focus();
    }

    // * Actualiza el contador rojo de la pestaña y el del título de pendientes
    function actualizarContador(total) {
        let badge = document.getElementById("badge-pendientes");
        badge.textContent = total;
        badge.setAttribute("aria-label", total + " registros pendientes");
        badge.hidden = total == 0;

        numPendientes.textContent = total;
        cajaPendientes.hidden = total == 0;
    }

    // * Una línea por registro pendiente:
    // * ● Marta Gómez Ruiz — marta@email.com
    // *   Registrada el 12/11/26 · Servicios: Clases de inglés, Clases de francés   [Aceptar] [Rechazar]
    function crearFilaPendiente(pendiente) {
        let nombre = pendiente.nombre + " " + pendiente.apellidos;

        let fila = crearElemento("div", "lista__fila");
        fila.dataset.estado = "SOLICITADO"; // ? Reutilizamos el color ámbar de "solicitado" para el punto

        let punto = crearElemento("span", "lista__punto");
        punto.setAttribute("aria-hidden", "true");
        fila.appendChild(punto);

        let texto = crearElemento("div", "lista__texto");

        let frase = crearElemento("p", "lista__frase");
        frase.appendChild(crearElemento("strong", "", nombre));
        frase.appendChild(crearElemento("span", "lista__secundario", " — " + pendiente.email));
        texto.appendChild(frase);

        let detalles = "Registrado el " + formatearFecha(pendiente.fecha_registro) +
            " · Servicios: " + (pendiente.servicios || "ninguno");
        texto.appendChild(crearElemento("p", "lista__detalles", detalles));
        fila.appendChild(texto);

        let lado = crearElemento("div", "lista__lado");

        let botonAceptar = crearBoton("Aceptar", "admin__boton admin__boton--ok");
        botonAceptar.onclick = async function () {
            let respuesta = await pedirApi("/api/admin/usuarios/" + pendiente.id_usuario, "PATCH", {aprobado: true});

            if (respuesta.ok) {
                mostrarMensaje(mensaje, nombre + " ya puede entrar en la plataforma", false);
                pintarTabla();
            } else {
                mostrarMensaje(mensaje, respuesta.error, true);
            }
        };

        let botonRechazar = crearBoton("Rechazar", "admin__boton admin__boton--peligro");
        botonRechazar.onclick = async function () {
            // ! Rechazar borra la cuenta: avisamos
            let aviso = "¿Rechazar el registro de " + nombre + "?\n\n" +
                "Se borrará su cuenta. Podrá volver a registrarse con el mismo email.";

            if (!confirm(aviso)) {
                return;
            }

            let respuesta = await pedirApi("/api/admin/usuarios/" + pendiente.id_usuario, "PATCH", {aprobado: false});

            if (respuesta.ok) {
                mostrarMensaje(mensaje, "Registro de " + nombre + " rechazado", false);
                pintarTabla();
            } else {
                mostrarMensaje(mensaje, respuesta.error, true);
            }
        };

        lado.appendChild(botonAceptar);
        lado.appendChild(botonRechazar);
        fila.appendChild(lado);

        return fila;
    }

    function pintarPendientes(pendientes) {
        listaPendientes.innerHTML = "";
        actualizarContador(pendientes.length);

        for (let pendiente of pendientes) {
            listaPendientes.appendChild(crearFilaPendiente(pendiente));
        }
    }

    async function pintarTabla() {
        let url = "/api/admin/usuarios";
        if (buscador.value.trim() != "") {
            url = url + "?busqueda=" + encodeURIComponent(buscador.value.trim());
        }

        let resultado = await pedirApi(url);
        filas.innerHTML = "";

        if (!resultado.ok) {
            mostrarMensaje(mensaje, resultado.error, true);
            return;
        }

        // * Primero los pendientes (arriba), luego la tabla de usuarios aprobados
        pintarPendientes(resultado.pendientes);

        if (resultado.usuarios.length == 0) {
            filas.appendChild(filaVacia("No hay usuarios que coincidan.", 6));
            return;
        }

        for (let usuario of resultado.usuarios) {
            let activo = usuario.activo == 1;
            let esYo = usuario.id_usuario == resultado.id_admin;

            let fila = crearElemento("tr", activo ? "" : "admin__fila--baja");

            fila.appendChild(crearCelda(usuario.nombre + " " + usuario.apellidos));
            fila.appendChild(crearCelda(usuario.email));
            fila.appendChild(crearCeldaInsignia(
                usuario.rol == "ADMIN" ? "Admin" : "Profesional",
                usuario.rol == "ADMIN" ? "admin__insignia--admin" : ""
            ));
            fila.appendChild(crearCelda(usuario.puntos_saldo));
            fila.appendChild(crearCeldaInsignia(
                activo ? "Activo" : "De baja",
                activo ? "admin__insignia--ok" : "admin__insignia--baja"
            ));

            let acciones = crearElemento("td", "admin__acciones");

            let botonEditar = crearBoton("Editar", "admin__boton");
            botonEditar.onclick = function () {
                modoEditar(usuario);
            };
            acciones.appendChild(botonEditar);

            // ! Al admin (yo) no le ponemos botones de baja ni de borrar
            if (!esYo) {
                let botonEstado = crearBoton(activo ? "Dar de baja" : "Reactivar", "admin__boton");
                botonEstado.onclick = async function () {
                    let nombre = usuario.nombre + " " + usuario.apellidos;
                    let texto = activo
                        ? "¿Dar de baja a " + nombre + "?\n\nNo podrá entrar y sus ofertas dejarán de salir en el buscador. Su historial se mantiene."
                        : "¿Reactivar a " + nombre + "?";

                    if (!confirm(texto)) {
                        return;
                    }

                    let respuesta = await pedirApi("/api/admin/usuarios/" + usuario.id_usuario, "PATCH", {activo: !activo});

                    if (respuesta.ok) {
                        mostrarMensaje(mensaje, activo ? "Usuario dado de baja" : "Usuario reactivado", false);
                        pintarTabla();
                    } else {
                        mostrarMensaje(mensaje, respuesta.error, true);
                    }
                };
                acciones.appendChild(botonEstado);

                let botonBorrar = crearBoton("Borrar", "admin__boton admin__boton--peligro");
                botonBorrar.onclick = async function () {
                    // ! Borrar es definitivo: avisamos bien
                    let texto = "¿Borrar DEFINITIVAMENTE a " + usuario.nombre + " " + usuario.apellidos + "?\n\n" +
                        "Se borrarán también sus ofertas, sus solicitudes y sus valoraciones. " +
                        "Si solo quieres que no entre, usa \"Dar de baja\".";

                    if (!confirm(texto)) {
                        return;
                    }

                    let respuesta = await pedirApi("/api/admin/usuarios/" + usuario.id_usuario, "DELETE");

                    if (respuesta.ok) {
                        mostrarMensaje(mensaje, "Usuario borrado", false);
                        modoCrear();
                        pintarTabla();
                    } else {
                        mostrarMensaje(mensaje, respuesta.error, true);
                    }
                };
                acciones.appendChild(botonBorrar);
            }

            fila.appendChild(acciones);
            filas.appendChild(fila);
        }
    }

    form.onsubmit = async function (ev) {
        ev.preventDefault();

        let id = form["id"].value;

        let datos = {
            nombre: form["nombre"].value.trim(),
            apellidos: form["apellidos"].value.trim(),
            email: form["email"].value.trim(),
            telefono: form["telefono"].value.trim(),
            puntos: Number(form["puntos"].value)
        };

        // * Comprobaciones rápidas (el PHP las vuelve a hacer)
        if (datos.nombre == "" || datos.apellidos == "") {
            mostrarMensaje(mensaje, "El nombre y los apellidos son obligatorios", true);
            return;
        }
        if (!form["email"].checkValidity() || datos.email == "") {
            mostrarMensaje(mensaje, "Escribe un email válido", true);
            return;
        }
        if (datos.puntos < 0) {
            mostrarMensaje(mensaje, "Los puntos no pueden ser negativos", true);
            return;
        }

        // * La contraseña solo al crear
        if (id == "") {
            datos.password = form["password"].value;
            if (datos.password.length < 8) {
                mostrarMensaje(mensaje, "La contraseña debe tener al menos 8 caracteres", true);
                return;
            }
        }

        let respuesta;
        if (id == "") {
            respuesta = await pedirApi("/api/admin/usuarios", "POST", datos);
        } else {
            respuesta = await pedirApi("/api/admin/usuarios/" + id, "PUT", datos);
        }

        if (respuesta.ok) {
            mostrarMensaje(mensaje, id == "" ? "Usuario creado" : "Usuario actualizado", false);
            modoCrear();
            pintarTabla();
        } else {
            mostrarMensaje(mensaje, respuesta.error, true);
        }
    };

    botonCancelar.onclick = modoCrear;

    // * Buscador: espera a que deje de escribir 300 ms para no hacer una petición por cada letra
    let temporizador = null;
    buscador.oninput = function () {
        clearTimeout(temporizador);
        temporizador = setTimeout(pintarTabla, 300);
    };

    pintarTabla();
}

// * ===== Sección: trueques =====
function iniciarTrueques() {
    let filtro = document.getElementById("tru-filtro");
    let lista = document.getElementById("tru-lista");
    let mensaje = document.getElementById("tru-mensaje");

    // * Texto bonito y color de cada estado (las claves son las del ENUM de la BD)
    let estados = {
        SOLICITADO: {texto: "Solicitado", clase: "admin__insignia--solicitado"},
        ACEPTADO: {texto: "Aceptado", clase: "admin__insignia--aceptado"},
        EN_EJECUCION: {texto: "En ejecución", clase: "admin__insignia--ejecucion"},
        COMPLETADO: {texto: "Completado", clase: "admin__insignia--ok"},
        RECHAZADO: {texto: "Rechazado", clase: "admin__insignia--baja"},
        CANCELADO: {texto: "Cancelado", clase: ""}
    };

    // ! Solo estos estados se pueden cancelar (el PHP lo vuelve a comprobar)
    let cancelables = ["SOLICITADO", "ACEPTADO", "EN_EJECUCION"];

    // * Una línea de la lista:
    // * ● Ana López contrata a Carlos Ruiz — Instalación eléctrica
    // *   12/11/26 9:00 · 1 h · 18 pts                  [Solicitado] [Cancelar]
    function crearFila(trueque) {
        let estado = estados[trueque.estado];
        let cliente = trueque.cliente_nombre + " " + trueque.cliente_apellidos;
        let prestador = trueque.prestador_nombre + " " + trueque.prestador_apellidos;

        let fila = crearElemento("div", "lista__fila");
        // * El CSS usa data-estado para el color del punto
        fila.dataset.estado = trueque.estado;

        // * Punto de color
        let punto = crearElemento("span", "lista__punto");
        punto.setAttribute("aria-hidden", "true");
        fila.appendChild(punto);

        // * Texto: la frase y los detalles
        let texto = crearElemento("div", "lista__texto");

        // ? Lo montamos por trozos (en vez de innerHTML) para que los nombres no puedan meter HTML
        let frase = crearElemento("p", "lista__frase");
        frase.appendChild(crearElemento("strong", "", cliente));
        frase.appendChild(document.createTextNode(" contrata a "));
        frase.appendChild(crearElemento("strong", "", prestador));
        frase.appendChild(crearElemento("span", "lista__secundario", " — " + trueque.servicio));
        texto.appendChild(frase);

        // ? Number() quita los ceros de "2.00" -> 2
        let detalles = formatearFecha(trueque.fecha_propuesta) + " · " +
            Number(trueque.horas_estimadas) + " h · " +
            trueque.puntos_totales + " pts";

        // * En ejecución interesa saber quién ha confirmado ya que está hecho
        if (trueque.estado == "EN_EJECUCION") {
            let cliOk = trueque.confirmado_cliente == 1 ? "✓" : "✗";
            let proOk = trueque.confirmado_prestador == 1 ? "✓" : "✗";
            detalles = detalles + " · Cliente " + cliOk + " · Prestador " + proOk;
        }

        texto.appendChild(crearElemento("p", "lista__detalles", detalles));
        fila.appendChild(texto);

        // * Derecha: estado y botón de cancelar
        let lado = crearElemento("div", "lista__lado");
        lado.appendChild(crearElemento("span", "admin__insignia " + estado.clase, estado.texto));

        if (cancelables.includes(trueque.estado)) {
            let botonCancelar = crearBoton("Cancelar", "admin__boton admin__boton--peligro");
            botonCancelar.onclick = async function () {
                let aviso = "¿Cancelar este trueque?\n\n" +
                    cliente + " → " + prestador +
                    " (" + trueque.servicio + ", " + trueque.puntos_totales + " puntos)\n\n" +
                    "No se mueven puntos, porque solo se transfieren al completarse.";

                if (!confirm(aviso)) {
                    return;
                }

                let respuesta = await pedirApi("/api/admin/trueques/" + trueque.id_solicitud, "PATCH");

                if (respuesta.ok) {
                    mostrarMensaje(mensaje, "Trueque cancelado", false);
                    pintarLista();
                } else {
                    mostrarMensaje(mensaje, respuesta.error, true);
                }
            };
            lado.appendChild(botonCancelar);
        }

        fila.appendChild(lado);
        return fila;
    }

    function pintarResumen(resumen) {
        document.getElementById("tru-total").textContent = resumen.total;
        document.getElementById("tru-completados").textContent = resumen.completados;
        document.getElementById("tru-en-curso").textContent = resumen.en_curso;
        document.getElementById("tru-puntos").textContent = resumen.puntos_intercambiados;
    }

    async function pintarLista() {
        let url = "/api/admin/trueques";
        if (filtro.value != "") {
            url = url + "?estado=" + filtro.value;
        }

        let resultado = await pedirApi(url);
        lista.innerHTML = "";

        if (!resultado.ok) {
            mostrarMensaje(mensaje, resultado.error, true);
            return;
        }

        pintarResumen(resultado.resumen);

        if (resultado.trueques.length == 0) {
            lista.appendChild(crearElemento("p", "admin__vacio", "No hay trueques con este estado."));
            return;
        }

        for (let trueque of resultado.trueques) {
            lista.appendChild(crearFila(trueque));
        }
    }

    filtro.onchange = pintarLista;

    pintarLista();
}

// * ===== Sección: reseñas =====
function iniciarResenas() {
    let filtro = document.getElementById("res-filtro");
    let lista = document.getElementById("res-lista");
    let mensaje = document.getElementById("res-mensaje");

    // * 4 -> "★★★★☆"
    function estrellas(puntuacion) {
        return "★".repeat(puntuacion) + "☆".repeat(5 - puntuacion);
    }

    // * Una línea de la lista:
    // * ★★★★★  Ana López sobre Laura Martínez — Reparación de tuberías
    // *        "Arregló la fuga en un momento."
    // *        10/10/26, 14:00                     [Quitar comentario] [Borrar]
    function crearFila(resena) {
        let autor = resena.autor_nombre + " " + resena.autor_apellidos;
        let valorado = resena.valorado_nombre + " " + resena.valorado_apellidos;
        let puntuacion = Number(resena.puntuacion);

        let fila = crearElemento("div", "lista__fila");
        // ! Las de 1-2 estrellas se marcan para que el admin las vea antes
        if (puntuacion <= 2) {
            fila.classList.add("lista__fila--aviso");
        }

        // * Estrellas (para el lector de pantalla decimos el número, no los símbolos)
        let caja = crearElemento("span", "lista__estrellas", estrellas(puntuacion));
        caja.setAttribute("aria-label", puntuacion + " de 5 estrellas");
        fila.appendChild(caja);

        // * Texto: quién valora a quién, el comentario y la fecha
        let texto = crearElemento("div", "lista__texto");

        let frase = crearElemento("p", "lista__frase");
        frase.appendChild(crearElemento("strong", "", autor));
        frase.appendChild(document.createTextNode(" sobre "));
        frase.appendChild(crearElemento("strong", "", valorado));
        frase.appendChild(crearElemento("span", "lista__secundario", " — " + resena.servicio));
        texto.appendChild(frase);

        if (resena.comentario) {
            texto.appendChild(crearElemento("p", "lista__comentario", "“" + resena.comentario + "”"));
        } else {
            texto.appendChild(crearElemento("p", "lista__comentario lista__comentario--vacio", "Sin comentario"));
        }

        texto.appendChild(crearElemento("p", "lista__detalles", formatearFecha(resena.fecha_valoracion)));
        fila.appendChild(texto);

        // * Derecha: acciones de moderación
        let lado = crearElemento("div", "lista__lado");

        // * Quitar comentario solo si tiene comentario
        if (resena.comentario) {
            let botonQuitar = crearBoton("Quitar comentario", "admin__boton");
            botonQuitar.onclick = async function () {
                let aviso = "¿Quitar el comentario de " + autor + "?\n\n" +
                    "“" + resena.comentario + "”\n\n" +
                    "Las " + puntuacion + " estrellas se mantienen, así que la media de " + valorado + " no cambia.";

                if (!confirm(aviso)) {
                    return;
                }

                let respuesta = await pedirApi("/api/admin/resenas/" + resena.id_valoracion, "PATCH");

                if (respuesta.ok) {
                    mostrarMensaje(mensaje, "Comentario retirado", false);
                    pintarLista();
                } else {
                    mostrarMensaje(mensaje, respuesta.error, true);
                }
            };
            lado.appendChild(botonQuitar);
        }

        let botonBorrar = crearBoton("Borrar", "admin__boton admin__boton--peligro");
        botonBorrar.onclick = async function () {
            // ! Borrar cambia la media del profesional: avisamos
            let aviso = "¿Borrar la reseña entera de " + autor + " sobre " + valorado + "?\n\n" +
                "Se borran también las " + puntuacion + " estrellas y la media de " + valorado + " se recalculará.\n" +
                "Si solo molesta el texto, usa \"Quitar comentario\".";

            if (!confirm(aviso)) {
                return;
            }

            let respuesta = await pedirApi("/api/admin/resenas/" + resena.id_valoracion, "DELETE");

            if (respuesta.ok) {
                mostrarMensaje(mensaje, "Reseña borrada", false);
                pintarLista();
            } else {
                mostrarMensaje(mensaje, respuesta.error, true);
            }
        };
        lado.appendChild(botonBorrar);

        fila.appendChild(lado);
        return fila;
    }

    function pintarResumen(resumen) {
        document.getElementById("res-total").textContent = resumen.total;
        // * 4.7 -> "4,7 ★" (con coma, como se escribe en España). Si no hay reseñas, "—"
        document.getElementById("res-media").textContent =
            resumen.media !== null ? resumen.media.toLocaleString("es-ES") + " ★" : "—";
        document.getElementById("res-bajas").textContent = resumen.bajas;
    }

    async function pintarLista() {
        let url = "/api/admin/resenas";
        if (filtro.value != "") {
            url = url + "?puntuacion=" + filtro.value;
        }

        let resultado = await pedirApi(url);
        lista.innerHTML = "";

        if (!resultado.ok) {
            mostrarMensaje(mensaje, resultado.error, true);
            return;
        }

        pintarResumen(resultado.resumen);

        if (resultado.resenas.length == 0) {
            lista.appendChild(crearElemento("p", "admin__vacio", "No hay reseñas con estas estrellas."));
            return;
        }

        for (let resena of resultado.resenas) {
            lista.appendChild(crearFila(resena));
        }
    }

    filtro.onchange = pintarLista;

    pintarLista();
}
