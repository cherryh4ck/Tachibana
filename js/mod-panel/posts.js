const postsLista = document.getElementById("modpanel-posts-lista");
const postsVacio = document.getElementById("modpanel-posts-vacio");
const postsCargarMas = document.getElementById("modpanel-posts-cargar-mas");
const postsBuscar = document.getElementById("modpanel-posts-buscar");
const postsCategoriaSelect = document.getElementById("modpanel-posts-categoria");
const postsEstadoSelect = document.getElementById("modpanel-posts-estado");
const postsBulkbar = document.getElementById("modpanel-posts-bulkbar");
const postsSeleccionarTodos = document.getElementById("modpanel-posts-seleccionar-todos");
const postsContador = document.getElementById("modpanel-posts-contador");

let postsOffset = 0;
let postsSeleccionados = new Set();
let postsBusquedaTimeout = null;
let postsPendientesBan = [];

function llamarModifyPost(postId, accion, extra) {
    const body = new URLSearchParams(Object.assign({ accion: accion, post_id: postId }, extra || {}));
    return fetch("php/admin/modify-post.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: body
    })
    .then((res) => res.json())
    .catch(() => ({ ok: false, mensaje: "Error de red." }));
}

function ejecutarEnLotePosts(ids, accion, extra) {
    return Promise.all(ids.map((id) => llamarModifyPost(id, accion, extra)));
}

function postsActualizarBulkbar() {
    const cantidad = postsSeleccionados.size;
    postsBulkbar.hidden = cantidad === 0;
    postsContador.textContent = cantidad + " seleccionado" + (cantidad != 1 ? "s" : "");
    postsSeleccionarTodos.checked = cantidad > 0 && cantidad === postsLista.children.length;
}

function postsCrearFila(post) {
    const fila = document.createElement("div");
    fila.className = "modpanel-fila";
    fila.dataset.id = post.id;

    let etiquetas = "";
    if (post.sticky) {
        etiquetas += "<span id='input-tag-amarillo'>Sticky</span>";
    }
    else {
        etiquetas += "<span id='input-tag-rojo'>/" + post.categoria + "/</span>";
    }
    if (post.archivado) etiquetas += "<span id='input-tag-user'>Archivado</span>";
    if (post.baneado) etiquetas += "<span id='input-tag-ban'>BAN</span>";

    const miniatura = post.miniatura || "resources/notfound.jpg";

    fila.innerHTML =
        '<label class="modpanel-checkbox"><input type="checkbox" class="modpanel-fila-checkbox"></label>' +
        '<img src="' + miniatura + '" alt="" onerror="this.src=\'resources/notfound.jpg\'">' +
        '<div class="modpanel-fila-info">' +
            '<p><a href="post.php?id=' + post.id + '" target="_blank"><b>' + post.titulo + '</b></a> ' + etiquetas + '</p>' +
            '<p>por @' + post.autor_username + '</p>' +
        '</div>' +
        '<div class="modpanel-fila-meta"><span>' + post.fecha_creacion.slice(0, 10) + '</span></div>' +
        '<div class="modpanel-fila-acciones"></div>';

    const acciones = fila.querySelector(".modpanel-fila-acciones");

    if (esAdmin && !post.baneado) {
        const stickyBoton = document.createElement("button");
        stickyBoton.textContent = post.sticky ? "Desfijar" : "Fijar";
        stickyBoton.addEventListener("click", () => {
            ejecutarEnLotePosts([post.id], "sticky", {}).then((resultados) => {
                modpanelResumirResultados(resultados, "post(s) actualizado(s)");
                postsCargar(true);
            });
        });
        acciones.appendChild(stickyBoton);
    }

    if (!post.baneado) {
        const archivarBoton = document.createElement("button");
        archivarBoton.textContent = post.archivado ? "Desarchivar" : "Archivar";
        archivarBoton.addEventListener("click", () => {
            ejecutarEnLotePosts([post.id], "archive", {}).then((resultados) => {
                modpanelResumirResultados(resultados, "post(s) actualizado(s)");
                postsCargar(true);
            });
        });
        acciones.appendChild(archivarBoton);

        const banearBoton = document.createElement("button");
        banearBoton.textContent = "Banear";
        banearBoton.addEventListener("click", () => postsAbrirDialogoBan([post.id]));
        acciones.appendChild(banearBoton);
    }

    const eliminarBoton = document.createElement("button");
    eliminarBoton.textContent = "Eliminar";
    eliminarBoton.className = "modpanel-peligro";
    eliminarBoton.addEventListener("click", () => {
        modpanelConfirmar("Eliminar post", 'Se eliminará "' + post.titulo + '" junto con sus comentarios e imágenes. Esta acción no se puede deshacer.', () => {
            ejecutarEnLotePosts([post.id], "delete", {}).then((resultados) => {
                modpanelResumirResultados(resultados, "post(s) eliminado(s)");
                postsCargar(true);
            });
        });
    });
    acciones.appendChild(eliminarBoton);

    fila.querySelector(".modpanel-fila-checkbox").addEventListener("change", (e) => {
        if (e.target.checked) {
            postsSeleccionados.add(post.id);
        }
        else {
            postsSeleccionados.delete(post.id);
        }
        postsActualizarBulkbar();
    });

    return fila;
}

function postsCargar(reset) {
    if (reset) {
        postsOffset = 0;
        postsLista.innerHTML = "";
        postsSeleccionados.clear();
        postsActualizarBulkbar();
    }

    const params = new URLSearchParams({ offset: postsOffset });
    if (postsBuscar.value.trim() !== "") params.set("q", postsBuscar.value.trim());
    if (postsCategoriaSelect.value !== "") params.set("categoria", postsCategoriaSelect.value);
    if (postsEstadoSelect.value !== "") params.set("estado", postsEstadoSelect.value);

    fetch("php/mod/list-posts.php?" + params.toString())
        .then((res) => res.json())
        .then((data) => {
            if (!data.ok) {
                notify(data.mensaje || "No se pudieron cargar los posts.", "error");
                return;
            }

            data.posts.forEach((post) => postsLista.appendChild(postsCrearFila(post)));

            postsOffset += data.posts.length;
            postsVacio.hidden = postsLista.children.length > 0;
            postsCargarMas.hidden = !data.hay_mas;
        })
        .catch(() => notify("No se pudieron cargar los posts.", "error"));
}

postsBuscar.addEventListener("input", () => {
    clearTimeout(postsBusquedaTimeout);
    postsBusquedaTimeout = setTimeout(() => postsCargar(true), 350);
});
postsCategoriaSelect.addEventListener("change", () => postsCargar(true));
postsEstadoSelect.addEventListener("change", () => postsCargar(true));
postsCargarMas.addEventListener("click", () => postsCargar(false));

postsSeleccionarTodos.addEventListener("change", (e) => {
    postsLista.querySelectorAll(".modpanel-fila-checkbox").forEach((casilla) => {
        casilla.checked = e.target.checked;
        const id = parseInt(casilla.closest(".modpanel-fila").dataset.id, 10);
        if (e.target.checked) {
            postsSeleccionados.add(id);
        }
        else {
            postsSeleccionados.delete(id);
        }
    });
    postsActualizarBulkbar();
});

const postsBulkSticky = document.getElementById("modpanel-posts-bulk-sticky");
if (postsBulkSticky) {
    postsBulkSticky.addEventListener("click", () => {
        ejecutarEnLotePosts(Array.from(postsSeleccionados), "sticky", {}).then((resultados) => {
            modpanelResumirResultados(resultados, "post(s) actualizado(s)");
            postsCargar(true);
        });
    });
}

document.getElementById("modpanel-posts-bulk-archive").addEventListener("click", () => {
    ejecutarEnLotePosts(Array.from(postsSeleccionados), "archive", {}).then((resultados) => {
        modpanelResumirResultados(resultados, "post(s) actualizado(s)");
        postsCargar(true);
    });
});

document.getElementById("modpanel-posts-bulk-ban").addEventListener("click", () => {
    postsAbrirDialogoBan(Array.from(postsSeleccionados));
});

document.getElementById("modpanel-posts-bulk-delete").addEventListener("click", () => {
    const ids = Array.from(postsSeleccionados);
    modpanelConfirmar("Eliminar posts", "Se eliminarán " + ids.length + " post(s) junto con sus comentarios e imágenes. Esta acción no se puede deshacer.", () => {
        ejecutarEnLotePosts(ids, "delete", {}).then((resultados) => {
            modpanelResumirResultados(resultados, "post(s) eliminado(s)");
            postsCargar(true);
        });
    });
});

function postsAbrirDialogoBan(ids) {
    postsPendientesBan = ids;
    document.getElementById("modpanel-ban-posts-motivo").value = "";
    document.getElementById("modpanel-ban-posts-recursos").checked = true;
    document.getElementById("modpanel-ban-posts-suspender").checked = false;

    const dialog = document.getElementById("dialog-modpanel-ban-posts");
    dialog.showModal();
    dialog.style.display = "flex";
}

document.getElementById("formulario-modpanel-ban-posts").addEventListener("submit", (e) => {
    e.preventDefault();

    let motivo = document.getElementById("modpanel-ban-posts-motivo").value.trim();
    if (motivo === "") motivo = "Sin especificar.";
    const eliminarRecursos = document.getElementById("modpanel-ban-posts-recursos").checked;
    const banearUsuario = document.getElementById("modpanel-ban-posts-suspender").checked;

    ejecutarEnLotePosts(postsPendientesBan, "ban", { motivo: motivo, eliminar_recursos: eliminarRecursos, banear_usuario: banearUsuario }).then((resultados) => {
        modpanelResumirResultados(resultados, "post(s) baneado(s)");
        postsCargar(true);

        const dialog = document.getElementById("dialog-modpanel-ban-posts");
        dialog.close();
        dialog.style.display = "none";
    });
});

postsCargar(true);
