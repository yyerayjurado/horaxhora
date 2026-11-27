// * Modal reutilizable, basado en el modal.js de clase
function Modal(titulo) {
    this.velo = document.createElement("div");
    this.modal = document.createElement("div");
    this.cabecera = document.createElement("div");
    this.tituloTexto = document.createElement("h2");
    this.cerrar = document.createElement("button");
    this.contenido = document.createElement("div");
    this.codigo = document.createElement("script");

    // * Clases para darle estilo desde modal.css
    this.velo.className = "modal-velo";
    this.modal.className = "modal";
    this.cabecera.className = "modal__cabecera";
    this.tituloTexto.className = "modal__titulo";
    this.cerrar.className = "modal__cerrar";
    this.contenido.className = "modal__contenido";

    this.tituloTexto.textContent = titulo;
    this.cerrar.textContent = "×";
    this.cerrar.type = "button";

    // * Montamos el modal y lo metemos en la página
    this.cabecera.appendChild(this.tituloTexto);
    this.cabecera.appendChild(this.cerrar);
    this.modal.appendChild(this.cabecera);
    this.modal.appendChild(this.contenido);
    document.body.appendChild(this.velo);
    document.body.appendChild(this.modal);

    // * Se cierra con la X o pinchando en el fondo gris
    let modal = this;
    this.cerrar.onclick = function () {
        modal.cerrarM();
    };
    this.velo.onclick = function () {
        modal.cerrarM();
    };
}

// * Quita el modal y el fondo de la página
Modal.prototype.cerrarM = function () {
    this.velo.remove();
    this.modal.remove();
};

// * Mete HTML dentro del modal
Modal.prototype.cargarHTML = function (html) {
    this.contenido.innerHTML = html;
};

// * Descarga un HTML del servidor y lo mete dentro del modal
Modal.prototype.cargarURL = async function (url) {
    let peticion = await fetch(url);

    if (!peticion.ok) {
        throw new Error("No se ha podido cargar " + url);
    }

    let html = await peticion.text();
    this.cargarHTML(html);
};

// * Carga el JS que da vida al contenido del modal
// ! Hay que llamarlo DESPUÉS de cargarURL, si no el JS no encuentra el formulario
// ! El "?v=" + la hora hace que el navegador lo descargue siempre nuevo y no use uno viejo de la caché
Modal.prototype.cargarLogica = function (url) {
    this.codigo.src = url + "?v=" + Date.now();
    this.modal.appendChild(this.codigo);
};

