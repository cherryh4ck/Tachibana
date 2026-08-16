const banear_form = document.getElementById("formulario-ban");
const banear_boton = document.getElementById("banear-enviar");
const user_id = document.getElementById("boton-bloquear-usuario").dataset.id;
var accion = "";

banear_form.addEventListener("submit", function(e){
    e.preventDefault();
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
});