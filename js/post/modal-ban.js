const ventana_ban = document.getElementsByClassName("contenido-setup")[0];
const ventana_ban_boton = document.getElementById("post-admin-banear");

ventana_ban_boton.addEventListener("click", function(e) {
    e.preventDefault();
    ventana_ban.showModal();
    ventana_ban.style.display = "flex";
});

ventana_ban.addEventListener("click", (event) => {
    // no tenía ni idea de esto kek
    const dialogRect = ventana_ban.getBoundingClientRect();
    const clickInside =
        event.clientX >= dialogRect.left &&
        event.clientX <= dialogRect.right &&
        event.clientY >= dialogRect.top &&
        event.clientY <= dialogRect.bottom;

    if (!clickInside) {
        ventana_ban.close();
    }
});

ventana_ban.addEventListener("close", () => {
    ventana_ban.style.display = "none";
});