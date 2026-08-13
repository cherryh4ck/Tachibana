const textarea = document.getElementById("post-comentarios-textarea");
const enviar = document.getElementById("post-comentarios-enviar");
const formularioComentar = document.getElementById("formulario-comentar");
const postComentarios = document.querySelector(".post-comentarios");
const postComentariosTitulo = document.getElementById("post-comentarios-titulo");

function agregarComentario(html) {
    const sinComentarios = document.getElementById("post-comentarios-no-comentarios");
    if (sinComentarios) {
        sinComentarios.remove();
    }

    postComentarios.insertAdjacentHTML("beforeend", html);

    if (postComentariosTitulo) {
        const match = postComentariosTitulo.textContent.match(/\((\d+)\)/);
        const nuevoTotal = match ? parseInt(match[1], 10) + 1 : 1;
        postComentariosTitulo.textContent = `Comentarios (${nuevoTotal})`;
    }
}

textarea.addEventListener("input", (e) => {
    if (textarea.value.trim() !== "") {
        enviar.disabled = false;
    } else {
        enviar.disabled = true;
    }
});

formularioComentar.addEventListener("submit", function(e){
    e.preventDefault();
    enviar.disabled = true;

    fetch("php/post/comentar.php", {
        method: "POST",
        body: new FormData(formularioComentar)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            notify("Comentario publicado", "exito");
            agregarComentario(data.html);
            formularioComentar.reset();
            data_div.style.display = "none";
            imagen_nombre_data.textContent = "";
            imagen_data.textContent = "";
        }
        else{
            notify("Hubo un error inesperado al comentar.", "error");
        }
        enviar.disabled = false;
    });
});