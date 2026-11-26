// * Crear oferta: elegir uno de mis servicios, ponerle precio y descripción, y publicarlo con fetch

const formOferta = document.querySelector('#form-oferta');
const selectServicio = document.querySelector('#oferta-servicio');
const inputPuntos = document.querySelector('#oferta-puntos');
const inputDescripcion = document.querySelector('#oferta-descripcion');
const ayuda = document.querySelector('#oferta-ayuda');
const mensaje = document.querySelector('#oferta-mensaje');
const sinOfertables = document.querySelector('#sin-ofertables');
const listaOfertas = document.querySelector('#lista-ofertas');
const sinOfertas = document.querySelector('#sin-ofertas');
const plantilla = document.querySelector('#plantilla-mi-oferta');

// * Los servicios que puedo ofertar (con su precio de referencia y sus márgenes)
let ofertables = [];

// * Pide al servidor lo que puedo ofertar y lo que ya oferto
async function cargarDatos() {
    try {
        const respuesta = await fetch('/api/mis-ofertas');
        const resultado = await respuesta.json();

        if (!resultado.ok) {
            mostrarMensaje(resultado.error, true);
            return;
        }

        ofertables = resultado.ofertables;
        pintarFormulario();
        pintarMisOfertas(resultado.ofertas);
    } catch (error) {
        // ! Error de red o el servidor no ha devuelto JSON
        mostrarMensaje('No se han podido cargar tus servicios.', true);
    }
}

// * ===== Formulario =====

function pintarFormulario() {
    // * Si no le queda ningún servicio por ofertar, escondemos el formulario
    formOferta.hidden = ofertables.length === 0;
    sinOfertables.hidden = ofertables.length > 0;

    selectServicio.innerHTML = '';

    ofertables.forEach((servicio) => {
        const opcion = document.createElement('option');
        opcion.value = servicio.id_servicio;
        opcion.textContent = `${servicio.nombre} (${servicio.categoria})`;
        selectServicio.appendChild(opcion);
    });

    if (ofertables.length > 0) {
        prepararPrecio();
    }
}

// * Al elegir un servicio: precio de referencia por defecto y márgenes en el input
function prepararPrecio() {
    const servicio = ofertables.find((s) => Number(s.id_servicio) === Number(selectServicio.value));
    const referencia = Number(servicio.puntos_hora_referencia);

    // * Entre la mitad y vez y media de la referencia (lo mismo que comprueba el PHP)
    const minimo = Math.round(referencia * 0.5);
    const maximo = Math.round(referencia * 1.5);

    inputPuntos.min = minimo;
    inputPuntos.max = maximo;
    inputPuntos.value = referencia;

    ayuda.textContent = `Referencia: ${referencia} pts/h · puedes cobrar de ${minimo} a ${maximo}`;
}

selectServicio.addEventListener('change', prepararPrecio);

formOferta.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    mensaje.hidden = true;

    const puntos = Number(inputPuntos.value);
    const descripcion = inputDescripcion.value.trim();

    // * Comprobaciones rápidas (el PHP las vuelve a hacer)
    if (puntos < Number(inputPuntos.min) || puntos > Number(inputPuntos.max)) {
        mostrarMensaje(`El precio tiene que estar entre ${inputPuntos.min} y ${inputPuntos.max} puntos por hora`, true);
        return;
    }
    if (descripcion.length < 10) {
        mostrarMensaje('Escribe una descripción de al menos 10 caracteres', true);
        return;
    }

    // ! Desactivamos el botón para que no se publique dos veces con doble clic
    const boton = formOferta.querySelector('button[type="submit"]');
    boton.disabled = true;

    try {
        const respuesta = await fetch('/api/ofertas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_servicio: Number(selectServicio.value),
                puntos_hora: puntos,
                descripcion: descripcion,
            }),
        });
        const resultado = await respuesta.json();

        if (!resultado.ok) {
            mostrarMensaje(resultado.error, true);
        } else {
            mostrarMensaje('¡Oferta publicada! Ya sale en el buscador de servicios.', false);
            formOferta.reset();
            // * Volvemos a pedir los datos: el servicio desaparece del desplegable y sale en "Mis ofertas"
            await cargarDatos();
        }
    } catch (error) {
        mostrarMensaje('Error de conexión con el servidor', true);
    }

    boton.disabled = false;
});

// * ===== Mis ofertas =====

function pintarMisOfertas(ofertas) {
    listaOfertas.innerHTML = '';
    sinOfertas.hidden = ofertas.length > 0;

    ofertas.forEach((oferta) => {
        const fila = plantilla.content.cloneNode(true);
        const activa = Number(oferta.activo) === 1;

        // ! textContent y no innerHTML: así no se puede colar código en los textos
        fila.querySelector('.mi-oferta__servicio').textContent = oferta.servicio;
        fila.querySelector('.mi-oferta__categoria').textContent = ` — ${oferta.categoria}`;
        fila.querySelector('.mi-oferta__descripcion').textContent = oferta.descripcion_personal || 'Sin descripción';
        fila.querySelector('.mi-oferta__precio').textContent =
            `${oferta.puntos_hora} pts/h · referencia ${oferta.puntos_hora_referencia} pts/h`;

        const estado = fila.querySelector('.mi-oferta__estado');
        estado.textContent = activa ? 'Activa' : 'Pausada';
        estado.classList.add(activa ? 'admin__insignia--ok' : 'admin__insignia--baja');

        listaOfertas.appendChild(fila);
    });
}

// * Mensaje verde (esError = false) o rojo (esError = true)
function mostrarMensaje(texto, esError) {
    mensaje.textContent = texto;
    mensaje.className = esError ? 'admin__mensaje admin__mensaje--error' : 'admin__mensaje';
    mensaje.hidden = false;
}

// * Primera carga al abrir la página
cargarDatos();
