(function () {
    const MAX_VISIBLES = 4;

    const contenedor = document.createElement("div");
    contenedor.className = "notif-contenedor";
    contenedor.popover = "manual";

    function montar() {
        if (!contenedor.isConnected) document.body.appendChild(contenedor);
        if (contenedor.matches(":popover-open")) contenedor.hidePopover();
        try { contenedor.showPopover(); } catch (e) {}
    }

    if (document.body) montar();
    else document.addEventListener("DOMContentLoaded", montar);

    const iconos = {
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg>',
        exito: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.3 2.3L16 9.5"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>',
        advertencia: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.8 21 19.5H3z"/><path d="M12 9.8v4.6"/><path d="M12 17.2h.01"/></svg>'
    };

    function expulsar(item) {
        if (item.dataset.saliendo) return;
        item.dataset.saliendo = "1";
        item.classList.remove("notif-entrar");
        item.classList.add("notif-salir");
        item.addEventListener("transitionend", () => item.remove(), { once: true });
    }

    function notify(mensaje, tipo = "info", duracion = 4200) {
        montar();

        const item = document.createElement("div");
        item.className = "notif notif-" + (iconos[tipo] ? tipo : "info");
        item.innerHTML =
            '<span class="notif-icono">' + (iconos[tipo] || iconos.info) + '</span>' +
            '<p class="notif-texto"></p>' +
            '<button class="notif-cerrar" type="button" aria-label="Cerrar notificación">' +
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>' +
            '</button>' +
            '<div class="notif-barra"></div>';

        item.querySelector(".notif-texto").textContent = mensaje;
        item.querySelector(".notif-barra").style.animationDuration = duracion + "ms";

        contenedor.prepend(item);

        let exceso = contenedor.children.length - MAX_VISIBLES;
        let sobrante = contenedor.lastElementChild;
        while (exceso > 0 && sobrante) {
            const anterior = sobrante.previousElementSibling;
            expulsar(sobrante);
            sobrante = anterior;
            exceso--;
        }

        requestAnimationFrame(() => requestAnimationFrame(() => item.classList.add("notif-entrar")));

        const temporizador = setTimeout(() => expulsar(item), duracion);
        item.querySelector(".notif-cerrar").addEventListener("click", () => {
            clearTimeout(temporizador);
            expulsar(item);
        });

        return () => {
            clearTimeout(temporizador);
            expulsar(item);
        };
    }

    window.notify = notify;
})();