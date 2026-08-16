boton_ascender.addEventListener("click", function(e){
    boton_ascender.disabled = true;

    fetch("php/admin/modify-user.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: "ascender", user_id: boton_ascender.dataset.id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            notify(data.mensaje, "exito");

            const tag_mod = document.getElementById("perfil-tag-mod");
            const tag_admin = document.getElementById("perfil-tag-admin");

            if (data.rol === "mod") {
                tag_mod.style.removeProperty("display");
                tag_admin.style.display = "none";
                boton_ascender.textContent = "Ascender a administrador";
                boton_ascender.dataset.objetivo = "admin";
                boton_ascender.disabled = false;
            }
            else if (data.rol === "admin") {
                tag_mod.style.display = "none";
                tag_admin.style.removeProperty("display");
                boton_ascender.remove();

                const boton_ban = document.getElementById("boton-bloquear-usuario");
                if (boton_ban) {
                    boton_ban.remove();
                }

                const dialog = document.getElementById("dialog-user-ban");
                if (dialog) {
                    dialog.remove();
                }
            }
        }
        else {
            notify(data.mensaje || "No se pudo ascender al usuario.", "error");
            boton_ascender.disabled = false;
        }
    })
    .catch(() => {
        notify("No se pudo ascender al usuario.", "error");
        boton_ascender.disabled = false;
    });
});