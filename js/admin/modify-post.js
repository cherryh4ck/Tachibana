const sticky_boton = document.getElementById("post-admin-fijar");
const banear_boton = document.getElementById("setup-enviar");
const post_titulo_fijado = document.getElementById("post-titulo-fijado");
const postId = sticky_boton.dataset.id;
var accion = "";

sticky_boton.addEventListener("click", function(e){
    e.preventDefault();
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
            console.log(data);
            if (data.value == 1){
                post_titulo_fijado.style.display = "inline";
            }
            else{
                post_titulo_fijado.style.display = "none";
            }
        }
        else {
            console.log("malito");
        }
        sticky_boton.disabled = false;
    })
    .catch(() => {
        sticky_boton.disabled = false;
    });
});

banear_boton.addEventListener("click", function(e){
    e.preventDefault();
    accion = "ban";
    var motivo = document.getElementById("ban-motivo").value;

    if (motivo === "") {
        motivo = "Sin especificar.";
    }

    banear_boton.disabled = true;

    fetch("php/admin/modify-post.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: accion, motivo: motivo, post_id: postId })
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
        banear_boton.disabled = false;
    });
});