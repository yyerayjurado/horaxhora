// * Lógica del registro: vale para la página /registro y para el modal
// ! Va dentro de una función porque en el modal este JS se carga cada vez que se abre,
// ! y con un "let" suelto daría error de "variable ya declarada"
function iniciarRegistro() {
    let form = document.getElementById("form-registro");
    let cajaError = document.getElementById("registro-error");
    let boton = form.querySelector(".auth__boton");
    let selectCategoria = document.getElementById("categoria");
    let listaServicios = document.getElementById("lista-servicios");

    // * Servicios de la categoría que se está viendo ahora mismo
    let serviciosCategoria = [];

    // ! Servicios elegidos de TODAS las categorías. Así no se pierden al cambiar de categoría
    // * Ejemplo: { "37": { nombre: "Manicura y pedicura", puntos: 12 } }
    let seleccionados = {};

    // * Texto que sale encima de los chips cuando no hay servicios que enseñar
    let textoAyuda = "Elige una categoría para ver sus servicios.";

    function mostrarError(texto) {
        cajaError.textContent = texto;
        cajaError.hidden = false;
    }

    // * Crea un chip: <label><input type="checkbox"><span>Nombre</span><small>10 pts/h</small></label>
    // ! La casilla está oculta con CSS, pero sigue ahí: al pinchar el chip se marca
    function crearChip(id, nombre, puntos, marcado) {
        let chip = document.createElement("label");
        chip.className = "registro__chip";

        let casilla = document.createElement("input");
        casilla.type = "checkbox";
        casilla.name = "servicios";
        casilla.value = id;
        casilla.checked = marcado;

        // * Al marcar o desmarcar: actualizamos los elegidos y volvemos a pintar la lista
        casilla.onchange = function () {
            if (casilla.checked) {
                seleccionados[id] = { nombre: nombre, puntos: puntos };
            } else {
                delete seleccionados[id];
            }

            pintarLista();

            // ? La lista se ha repintado: devolvemos el foco al chip (para quien usa el teclado)
            let nueva = listaServicios.querySelector('input[value="' + id + '"]');
            if (nueva) {
                nueva.focus();
            }
        };

        let textoNombre = document.createElement("span");
        textoNombre.textContent = nombre;

        let textoPrecio = document.createElement("small");
        textoPrecio.textContent = puntos + " pts/h";

        chip.appendChild(casilla);
        chip.appendChild(textoNombre);
        chip.appendChild(textoPrecio);

        return chip;
    }

    // * Pinta la lista: arriba los de la categoría actual, abajo TODOS los elegidos
    function pintarLista() {
        listaServicios.innerHTML = "";

        // * 1. Si no hay servicios de categoría que enseñar, ponemos el texto de ayuda
        if (serviciosCategoria.length == 0) {
            let ayuda = document.createElement("p");
            ayuda.className = "registro__ayuda";
            ayuda.textContent = textoAyuda;
            listaServicios.appendChild(ayuda);
        }

        // * 2. Servicios de la categoría actual que aún NO están elegidos
        for (let servicio of serviciosCategoria) {
            if (seleccionados[servicio.id_servicio] === undefined) {
                listaServicios.appendChild(
                    crearChip(servicio.id_servicio, servicio.nombre, servicio.puntos_hora_referencia, false)
                );
            }
        }

        // * 3. Abajo del todo, los elegidos (de esta categoría y de las anteriores), marcados en verde
        for (let id in seleccionados) {
            listaServicios.appendChild(
                crearChip(id, seleccionados[id].nombre, seleccionados[id].puntos, true)
            );
        }
    }

    // * Mostrar el mensaje de "solicitud enviada" en lugar del formulario
    function mostrarExito(nombre) {
        form.innerHTML = "";

        let caja = document.createElement("div");
        caja.className = "registro__exito";

        let icono = document.createElement("span");
        icono.className = "registro__exito-icono";
        icono.setAttribute("aria-hidden", "true");
        icono.textContent = "✓";

        let titulo = document.createElement("h2");
        titulo.className = "registro__exito-titulo";
        titulo.textContent = "¡Solicitud enviada, " + nombre + "!";

        let texto = document.createElement("p");
        texto.className = "registro__exito-texto";
        texto.textContent = "Un administrador revisará tu cuenta. Cuando la apruebe podrás iniciar sesión " +
            "con tu email y empezarás con 50 puntos de bienvenida.";

        let enlace = document.createElement("a");
        enlace.href = "/";
        enlace.className = "auth__boton";
        enlace.textContent = "Volver al inicio";

        caja.appendChild(icono);
        caja.appendChild(titulo);
        caja.appendChild(texto);
        caja.appendChild(enlace);
        form.appendChild(caja);
    }

    // * Al cambiar de categoría pedimos sus servicios al servidor
    selectCategoria.onchange = async function () {
        serviciosCategoria = [];

        if (selectCategoria.value == "") {
            textoAyuda = "Elige una categoría para ver sus servicios.";
            pintarLista();
            return;
        }

        textoAyuda = "Cargando servicios...";
        pintarLista();

        try {
            let respuesta = await fetch("/api/servicios?categoria=" + selectCategoria.value);
            let resultado = await respuesta.json();

            if (resultado.ok && resultado.servicios.length > 0) {
                serviciosCategoria = resultado.servicios;
            } else {
                textoAyuda = "Esta categoría todavía no tiene servicios.";
            }
        } catch (error) {
            textoAyuda = "No se han podido cargar los servicios. Inténtalo de nuevo.";
        }

        pintarLista();
    };

    form.onsubmit = async function (ev) {
        ev.preventDefault();
        cajaError.hidden = true;

        // * Los servicios elegidos salen del objeto (incluye los de todas las categorías)
        let servicios = Object.keys(seleccionados).map(Number);

        // * Comprobaciones rápidas antes de mandar nada al servidor
        if (form["nombre"].value.trim() == "" || form["apellidos"].value.trim() == "") {
            mostrarError("El nombre y los apellidos son obligatorios");
            return;
        }
        if (!form["email"].checkValidity() || form["email"].value == "") {
            mostrarError("Escribe un email válido");
            return;
        }
        if (form["password"].value.length < 8) {
            mostrarError("La contraseña debe tener al menos 8 caracteres");
            return;
        }
        if (form["password"].value != form["password2"].value) {
            mostrarError("Las contraseñas no coinciden");
            return;
        }
        if (servicios.length == 0) {
            mostrarError("Elige al menos un servicio que sepas hacer");
            return;
        }

        // * Datos que espera /api/registro
        let datos = {
            nombre: form["nombre"].value.trim(),
            apellidos: form["apellidos"].value,
            email: form["email"].value,
            telefono: form["telefono"].value,
            password: form["password"].value,
            password2: form["password2"].value,
            servicios: servicios
        };

        // * Desactivamos el botón para que no se registre dos veces por doble clic
        boton.disabled = true;

        try {
            let respuesta = await fetch("/api/registro", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datos)
            });
            let resultado = await respuesta.json();

            if (resultado.ok) {
                // ! Ya no entra directamente: la cuenta espera a que el admin la apruebe
                mostrarExito(datos.nombre);
            } else {
                mostrarError(resultado.error);
                boton.disabled = false;
            }
        } catch (error) {
            mostrarError("Error de conexión. Inténtalo de nuevo.");
            boton.disabled = false;
        }
    };
}

iniciarRegistro();
