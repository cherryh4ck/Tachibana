const formulario = document.getElementById("formulario-buscar-usuario");
const query = document.getElementById("nombreUsuario");

formulario.addEventListener("submit", function(event) {
    query.value = query.value.trim();
    if (query.value.length < 2 && query.value.trim().length !== 0) {
        event.preventDefault();
    }
    else if (query.value.trim().length == 0) {
        event.preventDefault();
        window.location.href = "perfiles.php";
    }
});
