const notifBuscar = document.getElementById("modpanel-notif-buscar");
const notifResultados = document.getElementById("modpanel-notif-resultados");
const notifSeleccionado = document.getElementById("modpanel-notif-seleccionado");
const notifUserIdInput = document.getElementById("modpanel-notif-user-id");
const notifTipoInput = document.getElementById("modpanel-notif-tipo");
const notifForm = document.getElementById("formulario-modpanel-notif");
const notifMensaje = document.getElementById("modpanel-notif-mensaje");

let notifBusquedaTimeout = null;

function notifSeleccionarUsuario(usuario) {
    notifUserIdInput.value = usuario.id;

    notifSeleccionado.innerHTML =
        '<img src="' + usuario.avatar + '" alt="" onerror="this.src=\'resources/avatar.png\'">' +
        '<span>' + usuario.nickname + ' <small>@' + usuario.username + '</small></span>';

    const quitarBoton = document.createElement("button");
    quitarBoton.type = "button";
    quitarBoton.textContent = "×";
    quitarBoton.addEventListener("click", () => {
        notifUserIdInput.value = "";
        notifSeleccionado.innerHTML = "";
        notifSeleccionado.hidden = true;
    });
    notifSeleccionado.appendChild(quitarBoton);

    notifSeleccionado.hidden = false;
    notifResultados.hidden = true;
    notifBuscar.value = "";
}

notifBuscar.addEventListener("input", () => {
    clearTimeout(notifBusquedaTimeout);
    const valor = notifBuscar.value.trim();

    if (valor.length < 2) {
        notifResultados.innerHTML = "";
        notifResultados.hidden = true;
        return;
    }

    notifBusquedaTimeout = setTimeout(() => {
        fetch("php/mod/list-users.php?" + new URLSearchParams({ q: valor, offset: 0 }))
            .then((res) => res.json())
            .then((data) => {
                if (!data.ok) return;

                notifResultados.innerHTML = "";

                if (data.usuarios.length === 0) {
                    notifResultados.hidden = true;
                    return;
                }

                data.usuarios.forEach((usuario) => {
                    const item = document.createElement("div");
                    item.className = "modpanel-autocomplete-item";
                    item.innerHTML =
                        '<img src="' + usuario.avatar + '" alt="" onerror="this.src=\'resources/avatar.png\'">' +
                        '<span>' + usuario.nickname + ' <small>@' + usuario.username + '</small></span>';
                    item.addEventListener("click", () => notifSeleccionarUsuario(usuario));
                    notifResultados.appendChild(item);
                });

                notifResultados.hidden = false;
            })
            .catch(() => {});
    }, 300);
});

document.addEventListener("click", (e) => {
    if (!notifResultados.contains(e.target) && e.target !== notifBuscar) {
        notifResultados.hidden = true;
    }
});

document.querySelectorAll(".modpanel-icon-opcion").forEach((boton) => {
    boton.addEventListener("click", () => {
        document.querySelectorAll(".modpanel-icon-opcion").forEach((b) => b.classList.remove("modpanel-icon-activo"));
        boton.classList.add("modpanel-icon-activo");
        notifTipoInput.value = boton.dataset.tipo;
    });
});

notifForm.addEventListener("submit", (e) => {
    e.preventDefault();

    if (notifUserIdInput.value === "") {
        notify("Tenés que elegir un usuario.", "error");
        return;
    }

    const boton = document.getElementById("modpanel-notif-enviar");
    boton.disabled = true;

    fetch("php/mod/send-notification.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            user_id: notifUserIdInput.value,
            mensaje: notifMensaje.value,
            tipo: notifTipoInput.value
        })
    })
    .then((res) => res.json())
    .then((data) => {
        if (data.ok) {
            notify(data.mensaje, "exito");
            notifMensaje.value = "";
            notifUserIdInput.value = "";
            notifSeleccionado.innerHTML = "";
            notifSeleccionado.hidden = true;
        }
        else {
            notify(data.mensaje || "No se pudo enviar la notificación.", "error");
        }
        boton.disabled = false;
    })
    .catch(() => {
        notify("No se pudo enviar la notificación.", "error");
        boton.disabled = false;
    });
});
