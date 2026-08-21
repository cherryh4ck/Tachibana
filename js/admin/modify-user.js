const banear_form = document.getElementById("formulario-ban");
const banear_boton = document.getElementById("banear-enviar");
const banear_modal_boton = document.getElementById("boton-bloquear-usuario");
const user_id = banear_modal_boton.dataset.id;

const tag_mod = document.getElementById("perfil-tag-mod");
const tag_ban = document.getElementById("input-tag-ban");
const descripcion_contenido = document.getElementById("perfil-descripcion-contenido");
const descripcion_suspendido = document.getElementById("perfil-descripcion-texto-suspendido");
const boton_ascender = document.getElementById("boton-ascender-usuario");

const delete_form = document.getElementById("formulario-delete");
const delete_boton = document.getElementById("delete-enviar");

const editar_form = document.getElementById("formulario-editar-usuario");
const editar_boton = document.getElementById("editar-usuario-guardar");
const editar_nickname_input = document.getElementById("editar-usuario-nickname");
const editar_username_input = document.getElementById("editar-usuario-username");
const editar_descripcion_input = document.getElementById("editar-usuario-descripcion");
const editar_avatar_preview = document.getElementById("editar-usuario-avatar-preview");
const editar_avatar_eliminar_boton = document.getElementById("editar-usuario-avatar-eliminar");

const perfil_avatar_grande = document.getElementById("perfil-avatar-grande");
const perfil_nickname_texto = document.getElementById("perfil-nickname-texto");
const perfil_username_texto = document.getElementById("contenido-perfil-bloque-info-username");

function actualizarUIBaneo(data) {
    if (data.accion === "ban") {
        tag_ban.style.removeProperty("display");
        descripcion_contenido.innerHTML = "";
        descripcion_contenido.style.display = "none";
        descripcion_suspendido.style.removeProperty("display");
        banear_modal_boton.textContent = "Desbanear usuario";
        banear_modal_boton.dataset.mode = "unban";

        if (data.rol === "user" && tag_mod.style.display !== "none") {
            tag_mod.style.display = "none";
            if (boton_ascender) {
                boton_ascender.textContent = "Ascender a moderador";
                boton_ascender.dataset.objetivo = "mod";
            }
        }
    }
    else if (data.accion === "unban") {
        tag_ban.style.display = "none";
        descripcion_contenido.innerHTML = data.descripcion_html;
        descripcion_contenido.style.removeProperty("display");
        descripcion_suspendido.style.display = "none";
        banear_modal_boton.textContent = "Suspender usuario";
        banear_modal_boton.dataset.mode = "ban";
    }
}

function banear() {
    var motivo = document.getElementById("ban-motivo").value;
    var fecha_expiracion = document.getElementById("ban-fecha-expiracion").value;

    if (motivo === "") {
        motivo = "Sin especificar.";
    }
    banear_boton.disabled = true;

    fetch("php/admin/modify-user.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: "ban", motivo: motivo, fecha_expiracion: fecha_expiracion, user_id: user_id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            actualizarUIBaneo(data);
            notify(data.mensaje, "exito");

            const dialog = document.getElementById("dialog-user-ban");
            if (dialog && dialog.open) {
                dialog.close();
                dialog.style.display = "none";
            }
        }
        else {
            notify(data.mensaje || "No se pudo actualizar el usuario.", "error");
        }
        banear_boton.disabled = false;
    })
    .catch(() => {
        notify("No se pudo actualizar el usuario.", "error");
        banear_boton.disabled = false;
    });
}

function eliminarUsuario() {
    delete_boton.disabled = true;

    fetch("php/admin/modify-user.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: "delete", user_id: user_id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            notify("El usuario ha sido eliminado", "exito");
            const dialog = document.getElementById("dialog-delete-usuario");
            if (dialog && dialog.open) {
                dialog.close();
                dialog.style.display = "none";
            }
        }
        else {
            notify("No se pudo eliminar el usuario", "error");
        }
        delete_boton.disabled = false;
    })
    .catch(() => {
        notify("No se pudo eliminar el usuario", "error");
        delete_boton.disabled = false;
    });
}

function editarDatosUsuario() {
    editar_boton.disabled = true;

    fetch("php/admin/modify-user.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            accion: "edit",
            user_id: user_id,
            nickname: editar_nickname_input.value,
            username: editar_username_input.value,
            descripcion: editar_descripcion_input.value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            notify(data.mensaje, "exito");

            perfil_nickname_texto.textContent = data.nickname;
            perfil_username_texto.textContent = "@" + data.username;
            descripcion_contenido.innerHTML = data.descripcion_html;

            const dialog = document.getElementById("dialog-editar-usuario");
            if (dialog && dialog.open) {
                dialog.close();
                dialog.style.display = "none";
            }
        }
        else {
            notify(data.mensaje || "No se pudieron guardar los cambios.", "error");
        }
        editar_boton.disabled = false;
    })
    .catch(() => {
        notify("No se pudieron guardar los cambios.", "error");
        editar_boton.disabled = false;
    });
}

function eliminarAvatarUsuario() {
    editar_avatar_eliminar_boton.disabled = true;

    fetch("php/admin/modify-user.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: "delete_avatar", user_id: user_id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            notify(data.mensaje, "exito");
            editar_avatar_preview.src = "resources/avatar.png";
            if (perfil_avatar_grande) {
                perfil_avatar_grande.src = "resources/avatar.png";
            }
        }
        else {
            notify(data.mensaje || "No se pudo eliminar el avatar.", "error");
            editar_avatar_eliminar_boton.disabled = false;
        }
    })
    .catch(() => {
        notify("No se pudo eliminar el avatar.", "error");
        editar_avatar_eliminar_boton.disabled = false;
    });
}

banear_modal_boton.addEventListener("click", function(e){
    if (banear_modal_boton.dataset.mode === "unban") {
        banear();
    }
});

banear_form.addEventListener("submit", function(e){
    e.preventDefault();
    banear();
});

delete_form.addEventListener("submit", function(e) {
    e.preventDefault();
    eliminarUsuario();
})

editar_form.addEventListener("submit", function(e) {
    e.preventDefault();
    editarDatosUsuario();
});

editar_avatar_eliminar_boton.addEventListener("click", function(e) {
    e.preventDefault();
    eliminarAvatarUsuario();
});