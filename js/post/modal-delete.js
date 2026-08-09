const ventana_delete = document.getElementsByClassName("contenido-setup")[1];
const ventana_delete_boton = document.getElementById("post-admin-eliminar");

ventana_delete_boton.addEventListener("click", function(e) {
    e.preventDefault();
    ventana_delete.showModal();
    ventana_delete.style.display = "flex";
});

ventana_delete.addEventListener("click", (event) => {
    // no tenía ni idea de esto kek
    const dialogRect = ventana_delete.getBoundingClientRect();
    const clickInside =
        event.clientX >= dialogRect.left &&
        event.clientX <= dialogRect.right &&
        event.clientY >= dialogRect.top &&
        event.clientY <= dialogRect.bottom;

    if (!clickInside) {
        ventana_delete.close();
        ventana_delete.style.display = "none";
    }
});