const sticky_boton = document.getElementById("post-admin-fijar");
const banear_form = document.getElementById("formulario-ban");
const banear_boton = document.getElementById("setup-enviar");
const eliminar_form = document.getElementById("formulario-delete");
const eliminar_boton = document.getElementById("setup-enviar-2");
const bloquear_comentarios_boton = document.getElementById("post-admin-bloquear-comentarios");
const post_categoria = document.getElementById("post-categoria") || document.getElementById("post-categoria-sticky");
const post_titulo_fijado = document.getElementById("post-titulo-fijado");
const postId = sticky_boton.dataset.id;
var accion = "";

sticky_boton.addEventListener("click", function(e){
    accion = "sticky";
    sticky_boton.disabled = true;

    fetch("php/admin/modify-post.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: accion, post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            if (data.value == 1){
                post_titulo_fijado.style.display = "inline";
                sticky_boton.textContent = "Desfijar post";
                post_categoria.textContent = "Sticky";
                post_categoria.id = "post-categoria-sticky";
                notify("Post fijado", "exito");
            }
            else{
                post_titulo_fijado.style.display = "none";
                sticky_boton.textContent = "Fijar post";
                post_categoria.textContent = categoria;
                post_categoria.id = "post-categoria";
                notify("Post desfijado");
            }
        }
        else {
            notify("No se pudo actualizar el post", "error");
        }
        sticky_boton.disabled = false;
    })
    .catch(() => {
        sticky_boton.disabled = false;
    });
});

banear_form.addEventListener("submit", function(e){
    e.preventDefault();
    accion = "ban";
    var motivo = document.getElementById("ban-motivo").value;
    var eliminar_recursos = document.getElementById("subir-eliminar-recursos").checked;
    var banear_usuario = document.getElementById("subir-suspender-cuenta").checked;

    if (motivo === "") {
        motivo = "Sin especificar.";
    }
    banear_boton.disabled = true;

    fetch("php/admin/modify-post.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: accion, motivo: motivo, eliminar_recursos: eliminar_recursos, banear_usuario: banear_usuario, post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            console.log(data);
            location.reload();
        }
        else {
            console.log("malito");
        }
        banear_boton.disabled = false;
    })
    .catch(() => {
        notify("Ocurrió un error inesperado", "error");
        banear_boton.disabled = false;
    });
});

eliminar_form.addEventListener("submit", function(e){
    e.preventDefault();
    accion = "delete";

    const dialog = document.getElementById("dialog-mod-delete");
    eliminar_boton.disabled = true;

    fetch("php/admin/modify-post.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: accion, post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            console.log(data);
            dialog.close();
            dialog.style.display = "none";
            document.cookie = "postEliminado=1; max-age=86400; path=/; Secure; SameSite=Strict";
            window.location.href = "index.php";
        }
        else {
            console.log("malito");
        }
    })
    .catch(() => {
        eliminar_boton.disabled = false;
    });
});

bloquear_comentarios_boton.addEventListener("click", function(e){
    accion = "archive";
    bloquear_comentarios_boton.disabled = true;

    fetch("php/admin/modify-post.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: accion, post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            console.log(data);
            if (data.value == 1){
                bloquear_comentarios_boton.textContent = "Desarchivar post";
                notify("Post archivado", "exito");
            }
            else {
                bloquear_comentarios_boton.textContent = "Archivar post";
                notify("Post desarchivado", "exito");
            }
            bloquear_comentarios_boton.disabled = false;
        }
        else {
            console.log("malito");
        }
    })
    .catch(() => {
        bloquear_comentarios_boton.disabled = false;
    });
});