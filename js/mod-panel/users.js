const usuariosLista = document.getElementById("modpanel-usuarios-lista");
const usuariosVacio = document.getElementById("modpanel-usuarios-vacio");
const usuariosCargarMas = document.getElementById("modpanel-usuarios-cargar-mas");
const usuariosBuscar = document.getElementById("modpanel-usuarios-buscar");
const usuariosRolSelect = document.getElementById("modpanel-usuarios-rol");
const usuariosEstadoSelect = document.getElementById("modpanel-usuarios-estado");
const usuariosBulkbar = document.getElementById("modpanel-usuarios-bulkbar");
const usuariosSeleccionarTodos = document.getElementById("modpanel-usuarios-seleccionar-todos");
const usuariosContador = document.getElementById("modpanel-usuarios-contador");

let usuariosOffset = 0;
let usuariosSeleccionados = new Set();
let usuariosBusquedaTimeout = null;
let usuarioEditando = null;
let usuariosPendientesBan = [];

function llamarModifyUser(userId, accion, extra) {
    const body = new URLSearchParams(Object.assign({ accion: accion, user_id: userId }, extra || {}));
    return fetch("php/admin/modify-user.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: body
    })
    .then((res) => res.json())
    .catch(() => ({ ok: false, mensaje: "Error de red." }));
}

function ejecutarEnLoteUsuarios(ids, accion, extra) {
    return Promise.all(ids.map((id) => llamarModifyUser(id, accion, extra)));
}

function usuariosActualizarBulkbar() {
    const cantidad = usuariosSeleccionados.size;
    usuariosBulkbar.hidden = cantidad === 0;
    usuariosContador.textContent = cantidad + " seleccionado" + (cantidad != 1 ? "s" : "");
    usuariosSeleccionarTodos.checked = cantidad > 0 && cantidad === usuariosLista.children.length;
}

function usuariosCrearFila(usuario) {
    const fila = document.createElement("div");
    fila.className = "modpanel-fila";
    fila.dataset.id = usuario.id;

    let etiquetas = "";
    if (usuario.rol === "admin") {
        etiquetas += "<span id='input-tag-admin' class='comentar-input-tag-op'>ADMIN</span>";
    }
    else if (usuario.rol === "mod") {
        etiquetas += "<span id='input-tag-mod' class='comentar-input-tag-op'>MOD</span>";
    }
    if (usuario.baneado) {
        etiquetas += "<span id='input-tag-ban' class='comentar-input-tag-op'>BAN</span>";
    }

    fila.innerHTML =
        '<label class="modpanel-checkbox"><input type="checkbox" class="modpanel-fila-checkbox"></label>' +
        '<img src="' + usuario.avatar + '" alt="" onerror="this.src=\'resources/avatar.png\'">' +
        '<div class="modpanel-fila-info">' +
            '<p><b>' + usuario.nickname + '</b> ' + etiquetas + '</p>' +
            '<p>@' + usuario.username + '</p>' +
        '</div>' +
        '<div class="modpanel-fila-meta"><span>' + usuario.fecha_creacion.slice(0, 10) + '</span></div>' +
        '<div class="modpanel-fila-acciones"></div>';

    const acciones = fila.querySelector(".modpanel-fila-acciones");
    const puedeActuar = usuario.id !== cuentaId && rango(usuario.rol) < rango(cuentaRol);

    if (puedeActuar) {
        const banBoton = document.createElement("button");
        banBoton.textContent = usuario.baneado ? "Desbanear" : "Suspender";
        banBoton.addEventListener("click", () => {
            if (usuario.baneado) {
                ejecutarEnLoteUsuarios([usuario.id], "ban", {}).then((resultados) => {
                    modpanelResumirResultados(resultados, "usuario(s) desbaneado(s)");
                    usuariosCargar(true);
                });
            }
            else {
                usuariosAbrirDialogoBan([usuario.id]);
            }
        });
        acciones.appendChild(banBoton);

        if (esAdmin && (usuario.rol === "user" || usuario.rol === "mod")) {
            const ascenderBoton = document.createElement("button");
            ascenderBoton.textContent = "Ascender";
            ascenderBoton.addEventListener("click", () => {
                ejecutarEnLoteUsuarios([usuario.id], "ascender", {}).then((resultados) => {
                    modpanelResumirResultados(resultados, "usuario(s) ascendido(s)");
                    usuariosCargar(true);
                });
            });
            acciones.appendChild(ascenderBoton);
        }

        const editarBoton = document.createElement("button");
        editarBoton.textContent = "Editar";
        editarBoton.addEventListener("click", () => usuariosAbrirDialogoEditar(usuario));
        acciones.appendChild(editarBoton);

        const eliminarBoton = document.createElement("button");
        eliminarBoton.textContent = "Eliminar";
        eliminarBoton.className = "modpanel-peligro";
        eliminarBoton.addEventListener("click", () => {
            modpanelConfirmar("Eliminar usuario", "Se eliminará a @" + usuario.username + " junto con todo su contenido. Esta acción no se puede deshacer.", () => {
                ejecutarEnLoteUsuarios([usuario.id], "delete", {}).then((resultados) => {
                    modpanelResumirResultados(resultados, "usuario(s) eliminado(s)");
                    usuariosCargar(true);
                });
            });
        });
        acciones.appendChild(eliminarBoton);
    }
    else {
        const etiquetaSinAcciones = document.createElement("span");
        etiquetaSinAcciones.className = "modpanel-fila-meta";
        etiquetaSinAcciones.textContent = usuario.id === cuentaId ? "Vos" : "Sin permisos";
        acciones.appendChild(etiquetaSinAcciones);
    }

    fila.querySelector(".modpanel-fila-checkbox").addEventListener("change", (e) => {
        if (e.target.checked) {
            usuariosSeleccionados.add(usuario.id);
        }
        else {
            usuariosSeleccionados.delete(usuario.id);
        }
        usuariosActualizarBulkbar();
    });

    return fila;
}

function usuariosCargar(reset) {
    if (reset) {
        usuariosOffset = 0;
        usuariosLista.innerHTML = "";
        usuariosSeleccionados.clear();
        usuariosActualizarBulkbar();
    }

    const params = new URLSearchParams({ offset: usuariosOffset });
    if (usuariosBuscar.value.trim() !== "") params.set("q", usuariosBuscar.value.trim());
    if (usuariosRolSelect.value !== "") params.set("rol", usuariosRolSelect.value);
    if (usuariosEstadoSelect.value !== "") params.set("estado", usuariosEstadoSelect.value);

    fetch("php/mod/list-users.php?" + params.toString())
        .then((res) => res.json())
        .then((data) => {
            if (!data.ok) {
                notify(data.mensaje || "No se pudieron cargar los usuarios.", "error");
                return;
            }

            data.usuarios.forEach((usuario) => usuariosLista.appendChild(usuariosCrearFila(usuario)));

            usuariosOffset += data.usuarios.length;
            usuariosVacio.hidden = usuariosLista.children.length > 0;
            usuariosCargarMas.hidden = !data.hay_mas;
        })
        .catch(() => notify("No se pudieron cargar los usuarios.", "error"));
}

usuariosBuscar.addEventListener("input", () => {
    clearTimeout(usuariosBusquedaTimeout);
    usuariosBusquedaTimeout = setTimeout(() => usuariosCargar(true), 350);
});
usuariosRolSelect.addEventListener("change", () => usuariosCargar(true));
usuariosEstadoSelect.addEventListener("change", () => usuariosCargar(true));
usuariosCargarMas.addEventListener("click", () => usuariosCargar(false));

usuariosSeleccionarTodos.addEventListener("change", (e) => {
    usuariosLista.querySelectorAll(".modpanel-fila-checkbox").forEach((casilla) => {
        casilla.checked = e.target.checked;
        const id = parseInt(casilla.closest(".modpanel-fila").dataset.id, 10);
        if (e.target.checked) {
            usuariosSeleccionados.add(id);
        }
        else {
            usuariosSeleccionados.delete(id);
        }
    });
    usuariosActualizarBulkbar();
});

document.getElementById("modpanel-usuarios-bulk-ban").addEventListener("click", () => {
    usuariosAbrirDialogoBan(Array.from(usuariosSeleccionados));
});

document.getElementById("modpanel-usuarios-bulk-unban").addEventListener("click", () => {
    ejecutarEnLoteUsuarios(Array.from(usuariosSeleccionados), "ban", {}).then((resultados) => {
        modpanelResumirResultados(resultados, "usuario(s) actualizado(s)");
        usuariosCargar(true);
    });
});

const usuariosBulkAscender = document.getElementById("modpanel-usuarios-bulk-ascender");
if (usuariosBulkAscender) {
    usuariosBulkAscender.addEventListener("click", () => {
        ejecutarEnLoteUsuarios(Array.from(usuariosSeleccionados), "ascender", {}).then((resultados) => {
            modpanelResumirResultados(resultados, "usuario(s) ascendido(s)");
            usuariosCargar(true);
        });
    });
}

document.getElementById("modpanel-usuarios-bulk-delete").addEventListener("click", () => {
    const ids = Array.from(usuariosSeleccionados);
    modpanelConfirmar("Eliminar usuarios", "Se eliminarán " + ids.length + " usuario(s) junto con todo su contenido. Esta acción no se puede deshacer.", () => {
        ejecutarEnLoteUsuarios(ids, "delete", {}).then((resultados) => {
            modpanelResumirResultados(resultados, "usuario(s) eliminado(s)");
            usuariosCargar(true);
        });
    });
});

function usuariosAbrirDialogoBan(ids) {
    usuariosPendientesBan = ids;
    document.getElementById("modpanel-ban-usuarios-motivo").value = "";
    document.getElementById("modpanel-ban-usuarios-expiracion").value = "";

    const dialog = document.getElementById("dialog-modpanel-ban-usuarios");
    dialog.showModal();
    dialog.style.display = "flex";
}

document.getElementById("formulario-modpanel-ban-usuarios").addEventListener("submit", (e) => {
    e.preventDefault();

    let motivo = document.getElementById("modpanel-ban-usuarios-motivo").value.trim();
    if (motivo === "") motivo = "Sin especificar.";
    const expira = document.getElementById("modpanel-ban-usuarios-expiracion").value;

    ejecutarEnLoteUsuarios(usuariosPendientesBan, "ban", { motivo: motivo, fecha_expiracion: expira }).then((resultados) => {
        modpanelResumirResultados(resultados, "usuario(s) suspendido(s)");
        usuariosCargar(true);

        const dialog = document.getElementById("dialog-modpanel-ban-usuarios");
        dialog.close();
        dialog.style.display = "none";
    });
});

function usuariosAbrirDialogoEditar(usuario) {
    usuarioEditando = usuario;
    document.getElementById("modpanel-editar-nickname").value = usuario.nickname;
    document.getElementById("modpanel-editar-username").value = usuario.username;
    document.getElementById("modpanel-editar-descripcion").value = usuario.descripcion || "";
    document.getElementById("modpanel-editar-avatar-preview").src = usuario.avatar;

    const dialog = document.getElementById("dialog-modpanel-editar-usuario");
    dialog.showModal();
    dialog.style.display = "flex";
}

document.getElementById("formulario-modpanel-editar-usuario").addEventListener("submit", (e) => {
    e.preventDefault();
    if (!usuarioEditando) return;

    llamarModifyUser(usuarioEditando.id, "edit", {
        nickname: document.getElementById("modpanel-editar-nickname").value,
        username: document.getElementById("modpanel-editar-username").value,
        descripcion: document.getElementById("modpanel-editar-descripcion").value
    }).then((data) => {
        if (data.ok) {
            notify(data.mensaje, "exito");
            usuariosCargar(true);

            const dialog = document.getElementById("dialog-modpanel-editar-usuario");
            dialog.close();
            dialog.style.display = "none";
        }
        else {
            notify(data.mensaje || "No se pudieron guardar los cambios.", "error");
        }
    });
});

document.getElementById("modpanel-editar-avatar-eliminar").addEventListener("click", () => {
    if (!usuarioEditando) return;

    llamarModifyUser(usuarioEditando.id, "delete_avatar", {}).then((data) => {
        if (data.ok) {
            notify(data.mensaje, "exito");
            document.getElementById("modpanel-editar-avatar-preview").src = "resources/avatar.png";
        }
        else {
            notify(data.mensaje || "No se pudo eliminar el avatar.", "error");
        }
    });
});

usuariosCargar(true);
