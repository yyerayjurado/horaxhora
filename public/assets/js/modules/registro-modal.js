// ! Todo va dentro de una función: si abres el modal dos veces, el JS se carga dos veces,
// ! y con un "let" suelto daría error de "variable ya declarada"
function iniciarRegistroModal() {
    let form = document.forms["registroModal"];
    let cajaError = document.getElementById("registro-error");

    function mostrarError(texto) {
        cajaError.textContent = texto;
        cajaError.hidden = false;
    }

    form.onsubmit = async function (ev) {
        ev.preventDefault();
        cajaError.hidden = true;

        // * Comprobaciones rápidas antes de mandar nada al servidor
        if (form["nombre"].value.trim() == "" || form["apellidos"].value.trim() == "") {
            mostrarError("El nombre y los apellidos son obligatorios");
            return;
        }
        if (!form["email"].checkValidity()) {
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

        // * Datos que espera /api/registro
        let datos = {
            nombre: form["nombre"].value,
            apellidos: form["apellidos"].value,
            email: form["email"].value,
            telefono: form["telefono"].value,
            password: form["password"].value,
            password2: form["password2"].value
        };

        // * Desactivamos el botón para que no se registre dos veces por doble clic
        form["guardar"].disabled = true;

        try {
            let respuesta = await fetch("/api/registro", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datos)
            });
            let resultado = await respuesta.json();

            if (resultado.ok) {
                // * Ya está logueado (lo hace el PHP), lo mandamos al inicio
                window.location.href = "/";
            } else {
                mostrarError(resultado.error);
                form["guardar"].disabled = false;
            }
        } catch (error) {
            mostrarError("Error de conexión. Inténtalo de nuevo.");
            form["guardar"].disabled = false;
        }
    };
}

iniciarRegistroModal();
