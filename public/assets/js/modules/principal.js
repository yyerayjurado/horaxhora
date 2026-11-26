document.addEventListener("DOMContentLoaded", function () {
    // * Todos los botones que abren el registro: el del header y los que tengan la clase js-abrir-registro
    let botones = document.querySelectorAll("#btnRegistrar, .js-abrir-registro");

    // ! Si ya está logueado no hay botones
    if (botones.length == 0) {
        return;
    }

    // ! Si ya estamos en /registro no abrimos el modal encima del formulario
    if (window.location.pathname == "/registro") {
        return;
    }

    async function abrirRegistro(ev) {
        ev.preventDefault();

        let modal = new Modal("Crear cuenta");
        // * El registro tiene dos columnas: el modal tiene que ser más ancho (modal.css)
        modal.modal.classList.add("modal--ancho");

        try {
            // ! El formulario lo devuelve el PHP en /registro/formulario
            await modal.cargarURL("/registro/formulario");
            modal.cargarLogica("/assets/js/modules/registro.js");
        } catch (error) {
            console.error(error);
            modal.cargarHTML("<p>No se ha podido cargar el formulario. Inténtalo de nuevo.</p>");
        }
    }

    for (let boton of botones) {
        boton.onclick = abrirRegistro;
    }
});
