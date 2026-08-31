const formulario_setup = document.getElementById("formulario-setup");
const boton = document.getElementById("setup-enviar");
const mensajeError = document.getElementById("mensaje-error");

const pdoStatus = document.getElementById("mensaje-pdo-status");
const gdStatus = document.getElementById("mensaje-gd-status");
const imagickStatus = document.getElementById("mensaje-imagick-status");


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

function chequearExtensiones(){
    const verde = "#87c57c";
    const rojo = "#c57c88";
    fetch("api/v1/extension/check.php")
    .then(response => response.json())
    .then(data => {
        if (data.pdo_mysql){
            pdoStatus.innerText = "OK";
            pdoStatus.style.color = verde;
            pdoStatus.title = "pdo_mysql está instalado en el servidor.";
        }
        else{
            gdStatus.innerText = "NO";
            gdStatus.style.color = rojo;
            gdStatus.title = "pdo_mysql no está instalado en el servidor, por lo que Tachibana no podrá instalarse.";
        }
        if (data.imagick){
            imagickStatus.innerText = "OK";
            imagickStatus.style.color = verde;
            imagickStatus.title = "Imagick está instalado en el servidor, lo que permite crear miniaturas de tipo GIF.";
        }
        else{
            imagickStatus.innerText = "NO";
            imagickStatus.style.color = rojo;
            imagickStatus.title = "Imagick no está instalado en el servidor. Si bien no afecta el funcionamiento de la página, no se podrán crear miniaturas de tipo GIF.";
        }
        if (data.gd){
            gdStatus.innerText = "OK";
            gdStatus.style.color = verde;
            gdStatus.title = "gd está instalado en el servidor.";
        }
        else{
            gdStatus.innerText = "NO";
            gdStatus.style.color = rojo;
            gdStatus.title = "gd no está instalado en el servidor, por lo que no se podrá postear.";
        }
    })
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

chequearExtensiones();