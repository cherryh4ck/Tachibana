function rango(rol) {
    if (rol === "admin") return 2;
    if (rol === "mod") return 1;
    return 0;
}

function modpanelConfigurarDialogo(id) {
    const dialog = document.getElementById(id);
    if (!dialog) return;

    dialog.addEventListener("click", (event) => {
        const rect = dialog.getBoundingClientRect();
        const dentro =
            event.clientX >= rect.left &&
            event.clientX <= rect.right &&
            event.clientY >= rect.top &&
            event.clientY <= rect.bottom;

        if (!dentro) {
            dialog.close();
        }
    });

    dialog.addEventListener("close", () => {
        dialog.style.display = "none";
    });
}

[
    "dialog-modpanel-confirmar",
    "dialog-modpanel-ban-usuarios",
    "dialog-modpanel-ban-posts",
    "dialog-modpanel-editar-usuario"
].forEach(modpanelConfigurarDialogo);

let modpanelConfirmarCallback = null;

function modpanelConfirmar(titulo, mensaje, callback) {
    document.getElementById("modpanel-confirmar-titulo").textContent = titulo;
    document.getElementById("modpanel-confirmar-mensaje").textContent = mensaje;
    modpanelConfirmarCallback = callback;

    const dialog = document.getElementById("dialog-modpanel-confirmar");
    dialog.showModal();
    dialog.style.display = "flex";
}

document.getElementById("formulario-modpanel-confirmar").addEventListener("submit", (e) => {
    e.preventDefault();

    const dialog = document.getElementById("dialog-modpanel-confirmar");
    dialog.close();
    dialog.style.display = "none";

    if (modpanelConfirmarCallback) {
        const callback = modpanelConfirmarCallback;
        modpanelConfirmarCallback = null;
        callback();
    }
});

function modpanelPlural(cantidad, singular, plural) {
    return cantidad + " " + (cantidad == 1 ? singular : plural);
}

function modpanelResumirResultados(resultados, singular, plural) {
    const exitosos = resultados.filter((r) => r.ok).length;
    const fallidos = resultados.length - exitosos;

    if (exitosos > 0) {
        notify(modpanelPlural(exitosos, singular, plural), "exito");
    }

    if (fallidos > 0) {
        notify(modpanelPlural(fallidos, "acción no se pudo completar.", "acciones no se pudieron completar."), fallidos == resultados.length ? "error" : "advertencia");
    }
}

const modpanelTabs = document.querySelectorAll(".modpanel-tab");
const modpanelPaneles = document.querySelectorAll(".modpanel-panel");

modpanelTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        modpanelTabs.forEach((t) => t.classList.remove("modpanel-tab-activa"));
        modpanelPaneles.forEach((p) => p.classList.remove("modpanel-panel-activo"));

        tab.classList.add("modpanel-tab-activa");
        document.getElementById("modpanel-" + tab.dataset.tab).classList.add("modpanel-panel-activo");
    });
});