const ventana_editar_usuario = document.getElementById("dialog-editar-usuario");
const ventana_editar_usuario_boton = document.getElementById("boton-modificar-datos");

ventana_editar_usuario_boton.addEventListener("click", function(e) {
    e.preventDefault();
    ventana_editar_usuario.showModal();
    ventana_editar_usuario.style.display = "flex";
});

ventana_editar_usuario.addEventListener("click", (event) => {
    const dialogRect = ventana_editar_usuario.getBoundingClientRect();
    const clickInside =
        event.clientX >= dialogRect.left &&
        event.clientX <= dialogRect.right &&
        event.clientY >= dialogRect.top &&
        event.clientY <= dialogRect.bottom;

    if (!clickInside) {
        ventana_editar_usuario.close();
    }
});

ventana_editar_usuario.addEventListener("close", () => {
    ventana_editar_usuario.style.display = "none";
});