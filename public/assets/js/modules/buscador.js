// * Buscador de servicios: pide las ofertas con fetch y las pinta sin recargar la página

const form = document.querySelector('#form-buscador');
const inputBusqueda = document.querySelector('#busqueda');
const selectOrden = document.querySelector('#orden');
const botonesCategoria = document.querySelectorAll('#filtros-categoria .filtro');
const lista = document.querySelector('#resultados-lista');
const contador = document.querySelector('#resultados-contador');
const vacio = document.querySelector('#resultados-vacio');
const plantilla = document.querySelector('#plantilla-oferta');

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

        // * Guardamos el id de la oferta en el botón para cuando hagamos las solicitudes
        tarjeta.querySelector('.oferta__boton').dataset.oferta = oferta.id_oferta;

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

// * Primera carga al abrir la página
cargarOfertas();
