// * Mi agenda: pide los trueques con fetch y los reparte en tres bloques:
// * "Te toca responder", "Próximos y en curso" e "Historial"

const plantilla = document.querySelector('#plantilla-tarjeta');
const mensaje = document.querySelector('#agenda-mensaje');

// * Los tres bloques: dónde van las tarjetas, el texto de "vacío" y el contador
const bloques = {
    responder: {
        lista: document.querySelector('#lista-responder'),
        vacio: document.querySelector('#vacio-responder'),
        contador: document.querySelector('#contador-responder')
    },
    proximos: {
        lista: document.querySelector('#lista-proximos'),
        vacio: document.querySelector('#vacio-proximos'),
        contador: document.querySelector('#contador-proximos')
    },
    historial: {
        lista: document.querySelector('#lista-historial'),
        vacio: document.querySelector('#vacio-historial'),
        contador: document.querySelector('#contador-historial')
    }
};

// * Texto y color de cada estado
const estados = {
    SOLICITADO: { texto: 'Solicitado', insignia: 'admin__insignia--solicitado' },
    ACEPTADO: { texto: 'Aceptado', insignia: 'admin__insignia--aceptado' },
    EN_EJECUCION: { texto: 'En curso', insignia: 'admin__insignia--ejecucion' },
    COMPLETADO: { texto: 'Completado', insignia: 'admin__insignia--ok' },
    RECHAZADO: { texto: 'Rechazado', insignia: 'admin__insignia--baja' },
    CANCELADO: { texto: 'Cancelado', insignia: '' }
};

const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
const diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

// * ===== Cargar y repartir =====

async function cargarAgenda() {
    try {
        const respuesta = await fetch('/api/agenda');
        const resultado = await respuesta.json();

        if (!resultado.ok) {
            mostrarMensaje(resultado.error, true);
            return;
        }

        // * "recibida" = me lo piden a mí (yo hago el servicio)
        // * "enviada"  = lo he pedido yo (me hacen el servicio)
        resultado.recibidas.forEach((trueque) => {
            trueque.tipo = 'recibida';
        });
        resultado.enviadas.forEach((trueque) => {
            trueque.tipo = 'enviada';
        });

        const todos = resultado.recibidas.concat(resultado.enviadas);

        // * Repartimos cada trueque en su bloque
        const responder = todos.filter((t) => t.tipo === 'recibida' && t.estado === 'SOLICITADO');
        const proximos = todos.filter((t) =>
            (t.tipo === 'enviada' && t.estado === 'SOLICITADO') || t.estado === 'ACEPTADO' || t.estado === 'EN_EJECUCION'
        );
        const historial = todos.filter((t) =>
            t.estado === 'COMPLETADO' || t.estado === 'RECHAZADO' || t.estado === 'CANCELADO'
        );

        // * Los que están por llegar, del más cercano al más lejano; el historial, del más nuevo al más viejo
        responder.sort(porFecha);
        proximos.sort(porFecha);
        historial.sort((a, b) => porFecha(b, a));

        pintarBloque(bloques.responder, responder);
        // * Si hay algo por responder, el contador se pone naranja para que se vea
        bloques.responder.contador.classList.toggle('agenda__contador--aviso', responder.length > 0);
        pintarBloque(bloques.proximos, proximos);
        pintarBloque(bloques.historial, historial);
    } catch (e) {
        // ! Sacamos el error real en la consola (F12) para poder ver qué ha fallado
        console.error(e);
        mostrarMensaje('No se ha podido cargar la agenda', true);
    }
}

function pintarBloque(bloque, trueques) {
    bloque.lista.innerHTML = '';
    bloque.contador.textContent = trueques.length;
    bloque.vacio.hidden = trueques.length > 0;

    trueques.forEach((trueque) => {
        bloque.lista.appendChild(crearTarjeta(trueque));
    });
}

// * ===== Tarjeta de un trueque =====

function crearTarjeta(trueque) {
    const tarjeta = plantilla.content.cloneNode(true).querySelector('.tarjeta');
    const estado = estados[trueque.estado];
    const fecha = new Date(trueque.fecha_propuesta.replace(' ', 'T'));
    const recibida = trueque.tipo === 'recibida';
    const persona = `${trueque.otro_nombre} ${trueque.otro_apellidos}`;
    const nombre = trueque.otro_nombre;


    // * Hoja de calendario
    tarjeta.querySelector('.tarjeta__dia').textContent = fecha.getDate();
    tarjeta.querySelector('.tarjeta__mes').textContent = meses[fecha.getMonth()];

    // * Quién hace el servicio
    const rol = tarjeta.querySelector('.tarjeta__rol');
    if (recibida) {
        rol.textContent = 'Tú haces el servicio';
        rol.classList.add('tarjeta__rol--prestas');
    } else {
        rol.textContent = 'Te hacen el servicio';
    }

    // * Insignia del estado
    const insignia = tarjeta.querySelector('.admin__insignia');
    insignia.textContent = estado.texto;
    if (estado.insignia !== '') {
        insignia.classList.add(estado.insignia);
    }

    // * Datos (textContent: así no se puede colar código en los textos)
    tarjeta.querySelector('.tarjeta__titulo').textContent = trueque.servicio;

    const quien = recibida ? `Te lo pide ${persona}` : `Lo hace ${persona}`;
    const horas = Number(trueque.horas_estimadas);
    // ? toLocaleString('es-ES') escribe 1.5 como "1,5"
    tarjeta.querySelector('.tarjeta__info').textContent =
        `${quien} · ${diasSemana[fecha.getDay()]}, ${hora(fecha)}–${horaFin(fecha, horas)} · ${horas.toLocaleString('es-ES')} h`;

    // * Puntos: en verde lo que ganas y en rojo lo que pagas (en gris si no se movieron)
    const puntos = tarjeta.querySelector('.tarjeta__puntos');
    if (trueque.estado === 'RECHAZADO' || trueque.estado === 'CANCELADO') {
        puntos.textContent = `${trueque.puntos_totales} pts`;
    } else if (recibida) {
        puntos.textContent = `+${trueque.puntos_totales} pts`;
        puntos.classList.add('tarjeta__puntos--gana');
    } else {
        puntos.textContent = `−${trueque.puntos_totales} pts`;
        puntos.classList.add('tarjeta__puntos--paga');
    }

    // * Lo que se ve y los botones dependen del estado y de quién soy
    const textoEstado = tarjeta.querySelector('.tarjeta__estado');
    const botones = tarjeta.querySelector('.tarjeta__botones');
    const error = tarjeta.querySelector('.tarjeta__error');

    // ? "accion": si es true, la frase se resalta porque te toca hacer algo a ti
    function ponerEstado(texto, accion) {
        textoEstado.textContent = texto;
        if (accion) {
            textoEstado.classList.add('tarjeta__estado--accion');
        }
    }

    if (trueque.estado === 'SOLICITADO' && recibida) {
        ponerEstado(`${nombre} quiere este servicio. ¿Puedes hacerlo ese día?`, true);
        botones.appendChild(crearBoton('Aceptar', 'btn btn--primario', 'aceptar', null));
        botones.appendChild(crearBoton('Rechazar', 'btn btn--secundario', 'rechazar',
            `¿Seguro que quieres rechazar la solicitud de ${nombre}?`));
    } else if (trueque.estado === 'SOLICITADO') {
        ponerEstado(`Esperando a que ${nombre} acepte tu solicitud. Todavía no pagas nada.`, false);
        botones.appendChild(crearBoton('Cancelar', 'btn btn--secundario', 'cancelar',
            '¿Seguro que quieres cancelar esta solicitud?'));
    } else if (trueque.estado === 'ACEPTADO' || trueque.estado === 'EN_EJECUCION') {
        pintarConfirmacion();
    } else if (trueque.estado === 'COMPLETADO') {
        pintarCompletado();
    } else if (trueque.estado === 'RECHAZADO') {
        ponerEstado(recibida ? 'Rechazaste esta solicitud.' : `${nombre} no pudo aceptar tu solicitud.`, false);
    } else {
        ponerEstado('Se canceló antes de hacerse. No se movieron puntos.', false);
    }

    // * Aceptado o en curso: doble confirmación de que el servicio está hecho
    function pintarConfirmacion() {
        // ? MySQL devuelve los BOOLEAN como 0 / 1, por eso usamos == 1
        const yoConfirmado = recibida ? trueque.confirmado_prestador == 1 : trueque.confirmado_cliente == 1;
        const otroConfirmado = recibida ? trueque.confirmado_cliente == 1 : trueque.confirmado_prestador == 1;

        if (yoConfirmado) {
            ponerEstado(`Has marcado que está terminado. Esperando a que ${nombre} lo confirme.`, false);
        } else if (otroConfirmado) {
            ponerEstado(`${nombre} ya ha marcado que está terminado. Confírmalo tú para cerrar el trueque.`, true);
        } else if (recibida) {
            ponerEstado('Quedáis citados. Cuando hayas hecho el servicio, márcalo como terminado.', false);
        } else {
            ponerEstado(`Quedáis citados. Cuando ${nombre} termine, márcalo como terminado y se le pagan los puntos.`, false);
        }

        if (!yoConfirmado) {
            botones.appendChild(crearBoton('Marcar como terminado', 'btn btn--primario', 'confirmar',
                '¿Confirmas que el servicio ya está hecho?'));
        }

        // * El cliente puede cancelar mientras nadie haya empezado a confirmar
        if (!recibida && trueque.estado === 'ACEPTADO') {
            botones.appendChild(crearBoton('Cancelar', 'btn btn--secundario', 'cancelar',
                '¿Seguro que quieres cancelar este trueque?'));
        }
    }

    // * Completado: se ve la valoración o, si soy el cliente y no he valorado, el botón de valorar
    function pintarCompletado() {
        ponerEstado(recibida ? `Hecho. Has ganado ${trueque.puntos_totales} pts.` : `Hecho. Has pagado ${trueque.puntos_totales} pts.`, false);

        const valoracion = tarjeta.querySelector('.tarjeta__valoracion');
        const formulario = tarjeta.querySelector('.tarjeta__valorar');

        if (trueque.puntuacion !== null) {
            const titulo = document.createElement('strong');
            titulo.textContent = recibida ? `${nombre} te ha valorado: ` : 'Tu valoración: ';

            const estrellas = document.createElement('span');
            estrellas.className = 'tarjeta__estrellas';
            estrellas.textContent = textoEstrellas(Number(trueque.puntuacion));

            valoracion.appendChild(titulo);
            valoracion.appendChild(estrellas);

            if (trueque.comentario !== null) {
                valoracion.appendChild(document.createElement('br'));
                valoracion.appendChild(document.createTextNode(`"${trueque.comentario}"`));
            }
            valoracion.hidden = false;
        } else if (!recibida) {
            ponerEstado(`¿Qué tal lo hizo ${nombre}? Deja tu valoración para ayudar a otros usuarios.`, true);

            // * El botón "Valorar" enseña u oculta el formulario
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'btn btn--primario';
            boton.textContent = 'Valorar';
            boton.addEventListener('click', () => {
                formulario.hidden = !formulario.hidden;
            });
            botones.appendChild(boton);

            formulario.addEventListener('submit', (evento) => {
                evento.preventDefault();
                enviar('valorar', {
                    puntuacion: Number(formulario.puntuacion.value),
                    comentario: formulario.comentario.value
                }, formulario.querySelector('button'));
            });
        } else {
            valoracion.textContent = `${nombre} todavía no te ha valorado.`;
            valoracion.hidden = false;
        }
    }

    // * Crea un botón que manda una acción al servidor
    // * aviso: texto del confirm() antes de hacerlo, o null si no hace falta preguntar
    function crearBoton(texto, clases, accion, aviso) {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = clases;
        boton.textContent = texto;

        boton.addEventListener('click', () => {
            if (aviso !== null && !confirm(aviso)) {
                return;
            }
            enviar(accion, null, boton);
        });

        return boton;
    }

    // * POST /api/agenda/{accion}/{id}. datos: el JSON a mandar (o null)
    async function enviar(accion, datos, boton) {
        // ! Desactivamos el botón para que no se mande dos veces con doble clic
        boton.disabled = true;
        error.hidden = true;

        const opciones = { method: 'POST' };
        if (datos !== null) {
            opciones.headers = { 'Content-Type': 'application/json' };
            opciones.body = JSON.stringify(datos);
        }

        try {
            const respuesta = await fetch(`/api/agenda/${accion}/${trueque.id_solicitud}`, opciones);
            const resultado = await respuesta.json();

            if (!resultado.ok) {
                error.textContent = resultado.error;
                error.hidden = false;
                boton.disabled = false;
                return;
            }

            // * Si el servidor manda el saldo nuevo, lo cambiamos en el header sin recargar
            if (resultado.saldo !== undefined) {
                document.getElementById('saldo-puntos').textContent = `${resultado.saldo} pts`;
            }

            mostrarMensaje(resultado.mensaje, false);
            cargarAgenda();
        } catch (e) {
            error.textContent = 'Error de conexión con el servidor';
            error.hidden = false;
            boton.disabled = false;
        }
    }

    return tarjeta;
}

// * ===== Funciones de ayuda =====

// * Para ordenar por fecha (de antes a después)
function porFecha(a, b) {
    return a.fecha_propuesta.localeCompare(b.fecha_propuesta);
}

// * Fecha -> "9:00"
function hora(fecha) {
    return `${fecha.getHours()}:${String(fecha.getMinutes()).padStart(2, '0')}`;
}

// * Hora a la que acaba: 9:00 + 1.5 h -> "10:30"
function horaFin(fecha, horas) {
    const fin = new Date(fecha.getTime() + horas * 3600000); // ? 3600000 ms = 1 hora
    return hora(fin);
}

// * 4 -> "★★★★☆"
function textoEstrellas(puntuacion) {
    return '★'.repeat(puntuacion) + '☆'.repeat(5 - puntuacion);
}

function mostrarMensaje(texto, esError) {
    mensaje.textContent = texto;
    mensaje.className = esError ? 'admin__mensaje admin__mensaje--error' : 'admin__mensaje';
    mensaje.hidden = false;
    // * Subimos para que se vea el mensaje
    window.scrollTo(0, 0);
}

// * Primera carga al abrir la página
cargarAgenda();
