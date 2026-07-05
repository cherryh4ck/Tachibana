const selector_categorias = document.getElementById("categoria-input-index");
const selector_orden = document.getElementById("categoria-input-categoria");
const tag_categoria = document.getElementById("input-tag-rojo-index")
const input_busqueda = document.getElementById("input-busqueda");
const tags_valor_busqueda_selector = document.getElementById("tags-valor-busqueda");

let categoria = "any";

function construirUrl(categoria, orden){
    let url = "?categoria=" + categoria + "&orden=" + orden;
    if (input_busqueda.value.length > 0){
        url += "&q=" + input_busqueda.value;
    }
    if (tags_valor_busqueda_selector.value.length > 0){
        url += "&tags=" + tags_valor_busqueda_selector.value;
    }
    return url;
}

selector_categorias.addEventListener("change", function() {
    const opcionSeleccionada = this.options[this.selectedIndex];
    const valorSeleccionado = opcionSeleccionada.value;

    categoria = valorSeleccionado;
    tag_categoria.textContent = "/" + valorSeleccionado + "/"; 
    window.location.replace(construirUrl(categoria, selector_orden.value.toUpperCase()));
});

selector_orden.addEventListener("change", function() {
    window.location.replace(construirUrl(selector_categorias.value, selector_orden.value.toUpperCase()));
});
