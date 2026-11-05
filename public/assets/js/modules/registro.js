// * Registro con fetch: envía el formulario sin recargar la página y enseña los errores

const form = document.querySelector('#form-registro');
const cajaError = document.querySelector('#registro-error');
const boton = form.querySelector('button[type="submit"]');

function mostrarError(mensaje) {
    cajaError.textContent = mensaje;
    cajaError.hidden = false;
}

function ocultarError() {
    cajaError.textContent = '';
    cajaError.hidden = true;
}

form.addEventListener('submit', async (evento) => {
    // * Evita que el formulario recargue la página
    evento.preventDefault();
    ocultarError();

    // * Recogemos los datos del formulario
    const datos = {
        nombre: form.nombre.value.trim(),
        apellidos: form.apellidos.value.trim(),
        email: form.email.value.trim(),
        telefono: form.telefono.value.trim(),
        password: form.password.value,
        password2: form.password2.value,
    };

    // * Validación rápida en el navegador (el servidor vuelve a comprobarlo todo)
    if (datos.password.length < 8) {
        mostrarError('La contraseña tiene que tener al menos 8 caracteres.');
        return;
    }

    if (datos.password !== datos.password2) {
        mostrarError('Las contraseñas no coinciden.');
        return;
    }

    // * Bloqueamos el botón para que no se envíe dos veces
    boton.disabled = true;
    boton.textContent = 'Creando cuenta...';

    try {
        const respuesta = await fetch('/api/registro', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos),
        });

        const resultado = await respuesta.json();

        if (resultado.ok) {
            // * Cuenta creada: vamos al inicio, ya con la sesión iniciada
            window.location.href = '/';
            return;
        }

        mostrarError(resultado.error);
    } catch (error) {
        // ! Error de red o el servidor no ha devuelto JSON
        mostrarError('No se ha podido conectar con el servidor.');
    }

    boton.disabled = false;
    boton.textContent = 'Crear cuenta';
});
