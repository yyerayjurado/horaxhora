// * Lógica del registro: vale para la página /registro y para el modal
// ! Va dentro de una función porque en el modal este JS se carga cada vez que se abre,
// ! y con un "let" suelto daría error de "variable ya declarada"
function iniciarRegistro() {
    let form = document.getElementById("form-registro");
    let cajaError = document.getElementById("registro-error");
    let boton = form.querySelector(".auth__boton");
    let selectCategoria = document.getElementById("categoria");
    let listaServicios = document.getElementById("lista-servicios");

    function mostrarError(texto) {
        cajaError.textContent = texto;
        cajaError.hidden = false;
    }

    function mostrarAyuda(texto) {
        listaServicios.innerHTML = "";
        let p = document.createElement("p");
        p.className = "registro__ayuda";
        p.textContent = texto;
        listaServicios.appendChild(p);
    }

    // * Al cambiar de categoría pedimos sus servicios al servidor y los pintamos como chips
    selectCategoria.onchange = async function () {
        if (selectCategoria.value == "") {
            mostrarAyuda("Elige una categoría para ver sus servicios.");
            return;
        }

        mostrarAyuda("Cargando servicios...");

        try {
            let respuesta = await fetch("/api/servicios?categoria=" + selectCategoria.value);
            let resultado = await respuesta.json();

            if (!resultado.ok || resultado.servicios.length == 0) {
                mostrarAyuda("Esta categoría todavía no tiene servicios.");
                return;
            }

            listaServicios.innerHTML = "";

            for (let servicio of resultado.servicios) {
                // * Cada chip es: <label><input type="checkbox"><span>Nombre</span><small>10 pts/h</small></label>
                // ! La casilla está oculta con CSS, pero sigue ahí: al pinchar el chip se marca
                let chip = document.createElement("label");
                chip.className = "registro__chip";

                let casilla = document.createElement("input");
                casilla.type = "checkbox";
                casilla.name = "servicios";
                casilla.value = servicio.id_servicio;

                let nombre = document.createElement("span");
                nombre.textContent = servicio.nombre;

                let precio = document.createElement("small");
                precio.textContent = servicio.puntos_hora_referencia + " pts/h";

                chip.appendChild(casilla);
                chip.appendChild(nombre);
                chip.appendChild(precio);
                listaServicios.appendChild(chip);
            }
        } catch (error) {
            mostrarAyuda("No se han podido cargar los servicios. Inténtalo de nuevo.");
        }
    };

    form.onsubmit = async function (ev) {
        ev.preventDefault();
        cajaError.hidden = true;

        // * Recogemos los servicios marcados
        let servicios = [];
        let marcados = form.querySelectorAll('input[name="servicios"]:checked');
        for (let casilla of marcados) {
            servicios.push(Number(casilla.value));
        }

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
            nombre: form["nombre"].value,
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
                // * El PHP ya lo ha dejado logueado, lo mandamos a su perfil para que vea sus servicios
                window.location.href = "/perfil";
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
