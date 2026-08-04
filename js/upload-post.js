const formSubir = document.getElementById("formulario-subir");
const botonSubir = document.getElementById("btn-enviar");

formSubir.addEventListener("submit", function(e){
    e.preventDefault();
    botonSubir.disabled = true;
    botonSubir.value = "Subiendo...";

    fetch("php/subida.php", {
        method: "POST",
        body: new FormData(formSubir)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            const postId = data.id;
            botonSubir.value = "Subido.";
            window.location.href = "post.php?id=" + postId;
        }
        else{
            botonSubir.disabled = false;
            botonSubir.value = "Subir";
        }
    })
});