const banear_form = document.getElementById("formulario-ban");
const banear_boton = document.getElementById("banear-enviar");
const banear_modal_boton = document.getElementById("boton-bloquear-usuario");
const user_id = banear_modal_boton.dataset.id;

const tag_mod = document.getElementById("perfil-tag-mod");
const tag_ban = document.getElementById("input-tag-ban");
const descripcion_contenido = document.getElementById("perfil-descripcion-contenido");
const descripcion_suspendido = document.getElementById("perfil-descripcion-texto-suspendido");
const boton_ascender = document.getElementById("boton-ascender-usuario");

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

banear_modal_boton.addEventListener("click", function(e){
    if (banear_modal_boton.dataset.mode === "unban") {
        banear();
    }
});

banear_form.addEventListener("submit", function(e){
    e.preventDefault();
    banear();
});