const banear_form = document.getElementById("formulario-ban");
const banear_boton = document.getElementById("banear-enviar");
const banear_modal_boton = document.getElementById("boton-bloquear-usuario");
const user_id = banear_modal_boton.dataset.id;
var data_mode = banear_modal_boton.dataset.mode;
var accion = "";

function banear() {
    accion = "ban";
    var motivo = document.getElementById("ban-motivo").value;
    var fecha_expiracion = document.getElementById("ban-fecha-expiracion").value;

    if (motivo === "") {
        motivo = "Sin especificar.";
    }
    banear_boton.disabled = true;

    fetch("php/admin/modify-user.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: accion, motivo: motivo, fecha_expiracion: fecha_expiracion, user_id: user_id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            console.log(data);
        }
        else {
            console.log("malito");
        }
        banear_boton.disabled = false;
    })
    .catch(() => {
        banear_boton.disabled = false;
    });
}

banear_modal_boton.addEventListener("click", function(e){
    if (data_mode === "unban") {
        banear();
    }
});

banear_form.addEventListener("submit", function(e){
    e.preventDefault();
    banear();
});