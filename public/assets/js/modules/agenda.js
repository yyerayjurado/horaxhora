// * Mi agenda: pide los trueques con fetch y los pinta en un calendario (vista de mes o de semana)

const titulo = document.querySelector('#calendario-titulo');
const calendario = document.querySelector('#calendario');
const botonAnterior = document.querySelector('#anterior');
const botonSiguiente = document.querySelector('#siguiente');
const botonesVista = document.querySelectorAll('.calendario__vista');
const listaPendientes = document.querySelector('#lista-pendientes');
const vacioPendientes = document.querySelector('#vacio-pendientes');
const plantillaPendiente = document.querySelector('#plantilla-pendiente');
const plantillaDetalle = document.querySelector('#plantilla-detalle');
const mensaje = document.querySelector('#agenda-mensaje');

// * Todos los trueques (recibidos y enviados juntos). Se rellena al cargar
let trueques = [];

// * Qué se está viendo: 'mes' o 'semana', y una fecha que cae dentro de ese mes o semana
const hoy = new Date();
let vista = 'mes';
let fechaVista = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

// * Pide los trueques al servidor
async function cargarAgenda() {
    try {
        const respuesta = await fetch('/api/agenda');
        const resultado = await respuesta.json();

        if (!resultado.ok) {
            mostrarMensaje(resultado.error, true);
            return;
        }

        // * Juntamos las dos listas y apuntamos en cada trueque de cuál venía
        trueques = [];
        resultado.recibidas.forEach((trueque) => {
            trueque.tipo = 'recibida';
            trueques.push(trueque);
        });
        resultado.enviadas.forEach((trueque) => {
            trueque.tipo = 'enviada';
            trueques.push(trueque);
        });

        pintarCalendario();
        pintarPendientes();
    } catch (error) {
        // ! Error de red o el servidor no ha devuelto JSON
        mostrarMensaje('No se ha podido cargar tu agenda.', true);
    }
}

// * Pinta la vista que toque
function pintarCalendario() {
    calendario.innerHTML = '';

    if (vista === 'mes') {
        pintarMes();
    } else {
        pintarSemana();
    }
}

// * ===== Vista de MES =====

function pintarMes() {
    const anio = fechaVista.getFullYear();
    const mes = fechaVista.getMonth(); // ? En JavaScript los meses van de 0 (enero) a 11 (diciembre)

    // * "noviembre de 2026" -> "Noviembre de 2026"
    const nombreMes = new Date(anio, mes, 1).toLocaleString('es-ES', { month: 'long', year: 'numeric' });
    titulo.textContent = nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1);

    // * El día 0 del mes siguiente es el último día de este mes
    const diasDelMes = new Date(anio, mes + 1, 0).getDate();

    // * Una lista como la de la semana, pero solo con los días que tienen algún trueque
    const lista = document.createElement('div');
    lista.className = 'semana';
    let hayTrueques = false;

    for (let dia = 1; dia <= diasDelMes; dia++) {
        const fecha = new Date(anio, mes, dia);

        if (truequesDelDia(fecha).length > 0) {
            lista.appendChild(crearDiaLista(fecha));
            hayTrueques = true;
        }
    }

    // * Si no hay ninguno, un mensaje
    if (!hayTrueques) {
        const vacio = document.createElement('p');
        vacio.className = 'admin__vacio';
        vacio.textContent = 'No tienes trueques este mes.';
        lista.appendChild(vacio);
    }

    calendario.appendChild(lista);
}


// * ===== Vista de SEMANA =====

function pintarSemana() {
    const lunes = lunesDe(fechaVista);
    const domingo = sumarDias(lunes, 6);

    // * "16 – 22 nov 2026", o "26 oct – 1 nov 2026" si la semana cambia de mes
    let desde = String(lunes.getDate());
    if (lunes.getMonth() !== domingo.getMonth()) {
        desde = lunes.toLocaleString('es-ES', { day: 'numeric', month: 'short' });
    }
    titulo.textContent = `${desde} – ${domingo.toLocaleString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' })}`;

    // * Una lista con los 7 días, uno debajo de otro (como una agenda de papel)
    const lista = document.createElement('div');
    lista.className = 'semana';

    for (let i = 0; i < 7; i++) {
        lista.appendChild(crearDiaLista(sumarDias(lunes, i)));
    }

    calendario.appendChild(lista);
}

// * Un día de la lista: cabecera "MIÉRCOLES 25 NOV" y debajo sus trueques
function crearDiaLista(fecha) {
    const dia = document.createElement('section');
    dia.className = 'semana__dia';
    if (esHoy(fecha)) {
        dia.classList.add('semana__dia--hoy');
    }

    const cabecera = document.createElement('h3');
    cabecera.className = 'semana__fecha';
    cabecera.textContent = fecha.toLocaleString('es-ES', { weekday: 'long', day: 'numeric', month: 'short' });
    if (esHoy(fecha)) {
        cabecera.textContent += ' · Hoy';
    }
    dia.appendChild(cabecera);

    const delDia = truequesDelDia(fecha);

    if (delDia.length === 0) {
        const vacio = document.createElement('p');
        vacio.className = 'semana__vacio';
        vacio.textContent = 'Nada este día';
        dia.appendChild(vacio);
    }

    delDia.forEach((trueque) => {
        dia.appendChild(crearFilaTrueque(trueque));
    });

    return dia;
}

// * Una fila: ● 10:00–13:30 · Pintura de interiores · ↓ Te lo pide Yeray · Aceptado
function crearFilaTrueque(trueque) {
    const fila = document.createElement('button');
    fila.type = 'button';
    fila.className = 'semana__trueque';
    fila.dataset.estado = trueque.estado;

    const horas = document.createElement('span');
    horas.className = 'semana__horas';
    horas.textContent = `${hora(trueque)}–${horaFin(trueque)}`;

    const servicio = document.createElement('strong');
    servicio.className = 'semana__servicio';
    servicio.textContent = trueque.servicio;

    const persona = document.createElement('span');
    persona.className = 'semana__persona';
    if (trueque.tipo === 'recibida') {
        persona.textContent = `↓ Te lo pide ${trueque.otro_nombre}`;
    } else {
        persona.textContent = `↑ Se lo pides a ${trueque.otro_nombre}`;
    }

    const estado = document.createElement('span');
    estado.className = `admin__insignia ${estados[trueque.estado].clase}`;
    estado.textContent = estados[trueque.estado].texto;

    fila.appendChild(horas);
    fila.appendChild(servicio);
    fila.appendChild(persona);
    fila.appendChild(estado);

    // * Al pulsar el trueque se abre su detalle
    fila.addEventListener('click', () => {
        abrirDetalle(trueque);
    });

    return fila;
}

// * Hora a la que acaba: "10:00" + 3.5 h -> "13:30"
function horaFin(trueque) {
    const inicio = new Date(trueque.fecha_propuesta.replace(' ', 'T'));
    const fin = new Date(inicio.getTime() + Number(trueque.horas_estimadas) * 3600000); // ? 3600000 ms = 1 hora
    const hh = String(fin.getHours()).padStart(2, '0');
    const mm = String(fin.getMinutes()).padStart(2, '0');
    return `${hh}:${mm}`;
}

// * ===== Botones =====

// * ‹ y ›: en mes se mueve un mes, en semana se mueve 7 días
botonAnterior.addEventListener('click', () => {
    if (vista === 'mes') {
        fechaVista = new Date(fechaVista.getFullYear(), fechaVista.getMonth() - 1, 1);
    } else {
        fechaVista = sumarDias(fechaVista, -7);
    }
    pintarCalendario();
});

botonSiguiente.addEventListener('click', () => {
    if (vista === 'mes') {
        fechaVista = new Date(fechaVista.getFullYear(), fechaVista.getMonth() + 1, 1);
    } else {
        fechaVista = sumarDias(fechaVista, 7);
    }
    pintarCalendario();
});

// * Mes / Semana
botonesVista.forEach((boton) => {
    boton.addEventListener('click', () => {
        cambiarVista(boton.dataset.vista);
    });
});

function cambiarVista(nuevaVista) {
    vista = nuevaVista;

    botonesVista.forEach((boton) => {
        boton.classList.toggle('is-active', boton.dataset.vista === vista);
    });

    pintarCalendario();
}

// * ===== Pendientes de responder =====

// * Solicitudes que me han hecho a mí y siguen en SOLICITADO
function pintarPendientes() {
    listaPendientes.innerHTML = '';

    const pendientes = trueques.filter((trueque) => trueque.tipo === 'recibida' && trueque.estado === 'SOLICITADO');
    vacioPendientes.hidden = pendientes.length > 0;

    pendientes.forEach((trueque) => {
        const fila = plantillaPendiente.content.cloneNode(true);

        fila.querySelector('.pendiente__persona').textContent = `${trueque.otro_nombre} ${trueque.otro_apellidos} te pide`;
        fila.querySelector('.pendiente__servicio').textContent = ` — ${trueque.servicio}`;
        fila.querySelector('.pendiente__detalles').textContent =
            `${formatearFecha(trueque.fecha_propuesta)} · ${Number(trueque.horas_estimadas)} h · ${trueque.puntos_totales} pts`;

        // * Al pulsar la fila se abre el detalle (con los botones de aceptar y rechazar)
        fila.querySelector('.lista__fila').addEventListener('click', () => {
            abrirDetalle(trueque);
        });

        listaPendientes.appendChild(fila);
    });
}

// * ===== Detalle de un trueque (modal) =====

// * Texto bonito y color de cada estado (las claves son las del ENUM de la BD)
const estados = {
    SOLICITADO: { texto: 'Solicitado', clase: 'admin__insignia--solicitado' },
    ACEPTADO: { texto: 'Aceptado', clase: 'admin__insignia--aceptado' },
    EN_EJECUCION: { texto: 'En ejecución', clase: 'admin__insignia--ejecucion' },
    COMPLETADO: { texto: 'Completado', clase: 'admin__insignia--ok' },
    RECHAZADO: { texto: 'Rechazado', clase: 'admin__insignia--baja' },
    CANCELADO: { texto: 'Cancelado', clase: '' },
};

function abrirDetalle(trueque) {
    const modal = new Modal('Detalle del trueque');
    modal.cargarHTML(plantillaDetalle.innerHTML);

    const detalle = modal.contenido;
    const estado = estados[trueque.estado];

    // * Insignia de estado
    const insignia = detalle.querySelector('.admin__insignia');
    insignia.textContent = estado.texto;
    if (estado.clase !== '') {
        insignia.classList.add(estado.clase);
    }

    // * Datos (textContent: así no se puede colar código en los textos)
    detalle.querySelector('.detalle__servicio').textContent = trueque.servicio;
    detalle.querySelector('.detalle__rol').textContent = trueque.tipo === 'recibida' ? 'Te lo pide' : 'Se lo has pedido a';
    detalle.querySelector('.detalle__persona').textContent = `${trueque.otro_nombre} ${trueque.otro_apellidos}`;
    detalle.querySelector('.detalle__fecha').textContent = formatearFecha(trueque.fecha_propuesta);
    detalle.querySelector('.detalle__horas').textContent = `${Number(trueque.horas_estimadas)} h`;
    detalle.querySelector('.detalle__puntos').textContent = `${trueque.puntos_totales} pts`;

    const botones = detalle.querySelector('.detalle__botones');
    const nota = detalle.querySelector('.detalle__nota');
    const error = detalle.querySelector('.detalle__error');

    // * Botones según quién soy y en qué estado está
    if (trueque.tipo === 'recibida' && trueque.estado === 'SOLICITADO') {
        // * Me lo piden a mí y aún no he contestado
        botones.appendChild(crearBotonAccion('Aceptar', 'btn btn--primario', 'aceptar', null));
        botones.appendChild(crearBotonAccion('Rechazar', 'btn btn--secundario', 'rechazar',
            '¿Seguro que quieres rechazar esta solicitud?'));
        nota.textContent = 'Si la aceptas, quedáis citados para ese día y hora.';
    } else if (trueque.tipo === 'enviada' && (trueque.estado === 'SOLICITADO' || trueque.estado === 'ACEPTADO')) {
        // * La he pedido yo y todavía no se ha hecho
        botones.appendChild(crearBotonAccion('Cancelar solicitud', 'btn btn--secundario', 'cancelar',
            '¿Seguro que quieres cancelar esta solicitud?'));
        nota.textContent = 'No se mueven puntos: solo se pagan cuando el servicio está hecho.';
    } else {
        nota.textContent = 'No hay nada que hacer con este trueque ahora mismo.';
    }

    // * Crea un botón que, al pulsarlo, manda la acción al servidor
    // * aviso: texto del confirm() antes de hacerlo, o null si no hace falta preguntar
    function crearBotonAccion(texto, clases, accion, aviso) {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = clases;
        boton.textContent = texto;

        boton.addEventListener('click', async () => {
            if (aviso !== null && !confirm(aviso)) {
                return;
            }

            // ! Desactivamos el botón para que no se mande dos veces con doble clic
            boton.disabled = true;
            error.hidden = true;

            try {
                const respuesta = await fetch(`/api/agenda/${accion}/${trueque.id_solicitud}`, { method: 'POST' });
                const resultado = await respuesta.json();

                if (!resultado.ok) {
                    error.textContent = resultado.error;
                    error.hidden = false;
                    boton.disabled = false;
                    return;
                }

                // * Todo bien: cerramos el modal y volvemos a pedir la agenda para repintarla
                modal.cerrarM();
                mostrarMensaje(resultado.mensaje, false);
                cargarAgenda();
            } catch (e) {
                error.textContent = 'Error de conexión con el servidor';
                error.hidden = false;
                boton.disabled = false;
            }
        });

        return boton;
    }
}

// * ===== Funciones de ayuda =====

// * Los trueques de un día, ordenados por hora
function truequesDelDia(fecha) {
    const texto = textoFecha(fecha);
    const delDia = trueques.filter((trueque) => trueque.fecha_propuesta.slice(0, 10) === texto);
    delDia.sort((a, b) => a.fecha_propuesta.localeCompare(b.fecha_propuesta));
    return delDia;
}

// * Fecha -> "2026-11-05", el mismo formato que la fecha que viene de MySQL
function textoFecha(fecha) {
    const mm = String(fecha.getMonth() + 1).padStart(2, '0');
    const dd = String(fecha.getDate()).padStart(2, '0');
    return `${fecha.getFullYear()}-${mm}-${dd}`;
}

// * El lunes de la semana de una fecha
function lunesDe(fecha) {
    const diasDesdeLunes = (fecha.getDay() + 6) % 7; // ? 0 = lunes... 6 = domingo
    return sumarDias(fecha, -diasDesdeLunes);
}

// * Devuelve una fecha nueva con X días más (o menos si X es negativo)
function sumarDias(fecha, dias) {
    return new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate() + dias);
}

function esHoy(fecha) {
    return textoFecha(fecha) === textoFecha(hoy);
}

// * ↓ si me lo piden a mí, ↑ si lo he pedido yo
function flecha(trueque) {
    return trueque.tipo === 'recibida' ? '↓' : '↑';
}

// * "2026-11-12 09:00:00" -> "09:00"
function hora(trueque) {
    return trueque.fecha_propuesta.slice(11, 16);
}

// * Texto que sale al pasar el ratón por encima
function textoDetalle(trueque) {
    const persona = `${trueque.otro_nombre} ${trueque.otro_apellidos}`;
    const quien = trueque.tipo === 'recibida' ? `Te lo pide ${persona}` : `Se lo has pedido a ${persona}`;
    return `${trueque.servicio} · ${quien} · ${Number(trueque.horas_estimadas)} h · ${trueque.puntos_totales} pts · ${trueque.estado}`;
}

// * "2026-11-12 09:00:00" (formato MySQL) -> "12/11/26, 9:00"
function formatearFecha(texto) {
    // ? El replace cambia el espacio por una T para que JavaScript entienda la fecha
    const fecha = new Date(texto.replace(' ', 'T'));
    return fecha.toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' });
}

// * Mensaje de arriba: verde (esError = false) o rojo (esError = true)
function mostrarMensaje(texto, esError) {
    mensaje.textContent = texto;
    mensaje.className = esError ? 'admin__mensaje admin__mensaje--error' : 'admin__mensaje';
    mensaje.hidden = false;
}

// * Primera carga al abrir la página
cargarAgenda();
