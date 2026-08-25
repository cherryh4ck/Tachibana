const tagsLista = document.getElementById("modpanel-tags-lista");
const tagsVacio = document.getElementById("modpanel-tags-vacio");
const tagsCargarMas = document.getElementById("modpanel-tags-cargar-mas");
const tagsBuscar = document.getElementById("modpanel-tags-buscar");
const tagsBulkbar = document.getElementById("modpanel-tags-bulkbar");
const tagsContador = document.getElementById("modpanel-tags-contador");

let tagsOffset = 0;
let tagsSeleccionados = new Set();
let tagsBusquedaTimeout = null;

function llamarEliminarTag(tagId) {
    return fetch("php/mod/manage-tag.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ accion: "delete", tag_id: tagId })
    })
    .then((res) => res.json())
    .catch(() => ({ ok: false, mensaje: "Error de red." }));
}

function tagsActualizarBulkbar() {
    const cantidad = tagsSeleccionados.size;
    tagsBulkbar.hidden = cantidad === 0;
    tagsContador.textContent = cantidad + " seleccionado" + (cantidad != 1 ? "s" : "");
}

function tagsCrearChip(tag) {
    const chip = document.createElement("div");
    chip.className = "modpanel-tag-chip";
    chip.dataset.id = tag.id;

    chip.innerHTML =
        '<input type="checkbox" class="modpanel-fila-checkbox">' +
        '<b>' + tag.nombre + '</b>' +
        '<span>' + tag.en_uso + ' post(s)</span>';

    const eliminarBoton = document.createElement("button");
    eliminarBoton.type = "button";
    eliminarBoton.textContent = "×";
    eliminarBoton.title = "Eliminar tag";
    eliminarBoton.addEventListener("click", () => {
        modpanelConfirmar("Eliminar tag", 'Se eliminará el tag "' + tag.nombre + '" de todos los posts que lo usan.', () => {
            llamarEliminarTag(tag.id).then((data) => {
                if (data.ok) {
                    notify(data.mensaje, "exito");
                    tagsCargar(true);
                }
                else {
                    notify(data.mensaje || "No se pudo eliminar el tag.", "error");
                }
            });
        });
    });
    chip.appendChild(eliminarBoton);

    chip.querySelector(".modpanel-fila-checkbox").addEventListener("change", (e) => {
        if (e.target.checked) {
            tagsSeleccionados.add(tag.id);
        }
        else {
            tagsSeleccionados.delete(tag.id);
        }
        tagsActualizarBulkbar();
    });

    return chip;
}

function tagsCargar(reset) {
    if (reset) {
        tagsOffset = 0;
        tagsLista.innerHTML = "";
        tagsSeleccionados.clear();
        tagsActualizarBulkbar();
    }

    const params = new URLSearchParams({ offset: tagsOffset });
    if (tagsBuscar.value.trim() !== "") params.set("q", tagsBuscar.value.trim());

    fetch("php/mod/list-tags.php?" + params.toString())
        .then((res) => res.json())
        .then((data) => {
            if (!data.ok) {
                notify(data.mensaje || "No se pudieron cargar los tags.", "error");
                return;
            }

            data.tags.forEach((tag) => tagsLista.appendChild(tagsCrearChip(tag)));

            tagsOffset += data.tags.length;
            tagsVacio.hidden = tagsLista.children.length > 0;
            tagsCargarMas.hidden = !data.hay_mas;
        })
        .catch(() => notify("No se pudieron cargar los tags.", "error"));
}

tagsBuscar.addEventListener("input", () => {
    clearTimeout(tagsBusquedaTimeout);
    tagsBusquedaTimeout = setTimeout(() => tagsCargar(true), 350);
});
tagsCargarMas.addEventListener("click", () => tagsCargar(false));

document.getElementById("modpanel-tags-bulk-delete").addEventListener("click", () => {
    const ids = Array.from(tagsSeleccionados);
    modpanelConfirmar("Eliminar tags", "Se eliminarán " + ids.length + " tag(s) de todos los posts que los usan.", () => {
        Promise.all(ids.map((id) => llamarEliminarTag(id))).then((resultados) => {
            modpanelResumirResultados(resultados, "tag(s) eliminado(s)");
            tagsCargar(true);
        });
    });
});

tagsCargar(true);
