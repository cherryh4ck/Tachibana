const formulario_setup = document.getElementById("formulario-setup");
const boton = document.getElementById("setup-enviar");
const mensajeError = document.getElementById("mensaje-error");

mensajeError.style.transition = "opacity 0.3s ease";
mensajeError.style.opacity = 0;

function mostrarError(texto){
    mensajeError.innerHTML = "<span>Error al instalar:</span> " + texto;
    mensajeError.style.display = "block";
    mensajeError.style.opacity = 0;
    requestAnimationFrame(() => {
        mensajeError.style.opacity = 1;
    });
}

formulario_setup.addEventListener("submit", function(e){
    e.preventDefault();

    boton.disabled = true;
    boton.value = "Instalando...";
    mensajeError.style.opacity = 0;

    fetch("php/setup.php", {
        method: "POST",
        body: new FormData(formulario_setup)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            boton.value = "Instalado.";
            window.location.reload();
        }
        else{
            mostrarError(data.mensaje);
            boton.disabled = false;
            boton.value = "Instalar";
        }
    })
    .catch(() => {
        mostrarError("No se pudo contactar al servidor, intentá de nuevo.");
        boton.disabled = false;
        boton.value = "Instalar";
    });
});