// * Buscador de servicios: pide las ofertas con fetch y las pinta sin recargar la página

const form = document.querySelector('#form-buscador');
const inputBusqueda = document.querySelector('#busqueda');
const selectOrden = document.querySelector('#orden');
const botonesCategoria = document.querySelectorAll('#filtros-categoria .filtro');
const lista = document.querySelector('#resultados-lista');
const contador = document.querySelector('#resultados-contador');
const vacio = document.querySelector('#resultados-vacio');
const plantilla = document.querySelector('#plantilla-oferta');
const plantillaSolicitud = document.querySelector('#plantilla-solicitud');

// * Id del usuario conectado ('' si no ha iniciado sesión)
const idUsuario = document.querySelector('.resultados').dataset.usuario;

// * Las ofertas que se están viendo ahora. Las guardamos para saber qué oferta se ha pulsado
let ofertasActuales = [];

// * Categoría elegida ahora mismo ('' = todas)
let categoriaActual = '';

// * Pide las ofertas al servidor con los filtros actuales
async function cargarOfertas() {
    // * URLSearchParams monta "?busqueda=...&categoria=...&orden=..." y codifica los acentos
    const parametros = new URLSearchParams({
        busqueda: inputBusqueda.value.trim(),
        categoria: categoriaActual,
        orden: selectOrden.value,
    });

    contador.textContent = 'Buscando...';

    try {
        const respuesta = await fetch(`/api/ofertas?${parametros}`);
        const resultado = await respuesta.json();

        pintarOfertas(resultado.ofertas);
    } catch (error) {
        // ! Error de red o el servidor no ha devuelto JSON
        lista.innerHTML = '';
        contador.textContent = 'No se han podido cargar los servicios.';
    }
}

// * Crea una tarjeta por oferta a partir del <template> del HTML
function pintarOfertas(ofertas) {
    lista.innerHTML = '';
    ofertasActuales = ofertas;

    contador.textContent = ofertas.length === 1 ? '1 oferta' : `${ofertas.length} ofertas`;
    vacio.hidden = ofertas.length > 0;

    ofertas.forEach((oferta) => {
        // * Copia el molde de la tarjeta
        const tarjeta = plantilla.content.cloneNode(true);

        // ! textContent y no innerHTML: así no se puede colar código en los textos
        tarjeta.querySelector('.oferta__categoria').textContent = oferta.categoria;
        tarjeta.querySelector('.oferta__puntos').textContent = `${oferta.puntos_hora} pts/h`;
        tarjeta.querySelector('.oferta__servicio').textContent = oferta.servicio;
        tarjeta.querySelector('.oferta__descripcion').textContent = oferta.descripcion_personal ?? '';
        tarjeta.querySelector('.oferta__avatar').textContent = oferta.profesional.charAt(0);
        tarjeta.querySelector('.oferta__nombre').textContent = oferta.profesional;
        tarjeta.querySelector('.oferta__valoracion').textContent = textoValoracion(oferta);
        tarjeta.querySelector('.oferta__profesional').href = `/profesional/${oferta.id_profesional}`;

        // * Guardamos el id de la oferta en el botón para saber cuál se ha pulsado
        const boton = tarjeta.querySelector('.oferta__boton');
        boton.dataset.oferta = oferta.id_oferta;

        // ! Tus propias ofertas no las puedes solicitar
        if (idUsuario !== '' && Number(idUsuario) === Number(oferta.id_profesional)) {
            boton.textContent = 'Es tu oferta';
            boton.disabled = true;
        }

        lista.appendChild(tarjeta);
    });
}

// * "★ 4,5 · 3 valoraciones" o "Sin valoraciones"
function textoValoracion(oferta) {
    const total = Number(oferta.num_valoraciones);

    if (total === 0) {
        return 'Sin valoraciones';
    }

    const media = Number(oferta.media_valoracion).toLocaleString('es-ES');
    return `★ ${media} · ${total} ${total === 1 ? 'valoración' : 'valoraciones'}`;
}

// * Espera a que el usuario deje de escribir 300 ms antes de buscar,
// * para no hacer una petición por cada letra
let temporizador;
inputBusqueda.addEventListener('input', () => {
    clearTimeout(temporizador);
    temporizador = setTimeout(cargarOfertas, 300);
});

selectOrden.addEventListener('change', cargarOfertas);

// * Al pulsar Enter no se recarga la página
form.addEventListener('submit', (evento) => {
    evento.preventDefault();
    cargarOfertas();
});

// * Filtros de categoría: marca el pulsado y vuelve a buscar
botonesCategoria.forEach((boton) => {
    boton.addEventListener('click', () => {
        botonesCategoria.forEach((b) => b.classList.remove('is-active'));
        boton.classList.add('is-active');

        categoriaActual = boton.dataset.categoria;
        cargarOfertas();
    });
});

// * ===== Solicitar un servicio =====

// * Un solo listener para todas las tarjetas (se crean y se borran al buscar)
lista.addEventListener('click', (evento) => {
    // ? closest() busca el botón aunque se pulse en el texto de dentro
    const boton = evento.target.closest('button.oferta__boton');

    if (boton === null || boton.disabled) {
        return;
    }

    const oferta = ofertasActuales.find((o) => Number(o.id_oferta) === Number(boton.dataset.oferta));
    abrirSolicitud(oferta);
});

// * Abre el modal con el formulario de solicitud de una oferta
function abrirSolicitud(oferta) {
    const modal = new Modal('Solicitar servicio');
    modal.cargarHTML(plantillaSolicitud.innerHTML);

    const formSolicitud = modal.contenido.querySelector('#form-solicitud');
    const inputFecha = formSolicitud.querySelector('#solicitud-fecha');
    const inputHora = formSolicitud.querySelector('#solicitud-hora');
    const inputHoras = formSolicitud.querySelector('#solicitud-horas');
    const total = formSolicitud.querySelector('#solicitud-total');
    const error = formSolicitud.querySelector('#solicitud-error');
    const botonEnviar = formSolicitud.querySelector('button[type="submit"]');

    // * Resumen: qué servicio, a quién y a cuánto
    formSolicitud.querySelector('.solicitud__servicio').textContent = oferta.servicio;
    formSolicitud.querySelector('.solicitud__profesional').textContent =
        `con ${oferta.profesional} · ${oferta.puntos_hora} pts/h`;

    // * No se puede elegir un día que ya ha pasado: el mínimo es mañana
    const manana = new Date();
    manana.setDate(manana.getDate() + 1);
    inputFecha.min = fechaInput(manana);
    inputFecha.value = fechaInput(manana);

    // * Total de puntos = puntos por hora × horas (se recalcula al cambiar las horas)
    function calcularTotal() {
        const horas = Number(inputHoras.value);
        total.textContent = `${Math.round(oferta.puntos_hora * horas)} puntos`;
    }
    calcularTotal();
    inputHoras.addEventListener('input', calcularTotal);

    formSolicitud.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        error.hidden = true;

        // * Comprobaciones rápidas (el PHP las vuelve a hacer)
        const horas = Number(inputHoras.value);
        if (inputFecha.value === '' || inputHora.value === '') {
            mostrarErrorSolicitud(error, 'Elige el día y la hora');
            return;
        }
        if (horas < 0.5 || horas > 8) {
            mostrarErrorSolicitud(error, 'Las horas tienen que estar entre 0,5 y 8');
            return;
        }

        // ! Desactivamos el botón para que no se envíe dos veces con doble clic
        botonEnviar.disabled = true;

        try {
            const respuesta = await fetch('/api/solicitudes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_oferta: oferta.id_oferta,
                    fecha: inputFecha.value,
                    hora: inputHora.value,
                    horas: horas,
                }),
            });
            const resultado = await respuesta.json();

            if (!resultado.ok) {
                mostrarErrorSolicitud(error, resultado.error);
                botonEnviar.disabled = false;
                return;
            }

            // * Todo bien: cambiamos el formulario por un mensaje de confirmación
            modal.cargarHTML(`
                <div class="solicitud__ok">
                    <p class="solicitud__ok-icono">✓</p>
                    <p><strong>¡Solicitud enviada!</strong></p>
                    <p>Ya la tienes en tu agenda como «Solicitado» hasta que el profesional la acepte o la rechace.</p>
                    <a href="/agenda" class="btn btn--primario">Ir a mi agenda</a>
                </div>
            `);
        } catch (e) {
            mostrarErrorSolicitud(error, 'Error de conexión con el servidor');
            botonEnviar.disabled = false;
        }
    });
}

function mostrarErrorSolicitud(elemento, texto) {
    elemento.textContent = texto;
    elemento.hidden = false;
}

// * Fecha -> "2026-11-25" (el formato que entiende un <input type="date">)
function fechaInput(fecha) {
    const mm = String(fecha.getMonth() + 1).padStart(2, '0');
    const dd = String(fecha.getDate()).padStart(2, '0');
    return `${fecha.getFullYear()}-${mm}-${dd}`;
}

// * Primera carga al abrir la página
cargarOfertas();
