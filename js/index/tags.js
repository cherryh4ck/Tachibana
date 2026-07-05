const tags_valor_busqueda = document.getElementById("tags-valor-busqueda");
const input_tag_busqueda = document.getElementById("input-tag-busqueda");
const tags_populares = document.getElementsByClassName("tag-popular");
const tags_seleccionados_remover = document.getElementsByClassName("tag-seleccionado-remover");

function tagsActuales(){
    if (tags_valor_busqueda.value.length > 0){
        return tags_valor_busqueda.value.split(",");
    }
    return [];
}

function irConTags(tags){
    const url = new URL(window.location.href);
    if (tags.length > 0){
        url.searchParams.set("tags", tags.join(","));
    }
    else{
        url.searchParams.delete("tags");
    }
    window.location.replace(url.pathname + url.search);
}

for (let boton of tags_populares){
    boton.addEventListener("click", function(){
        const tag = this.dataset.tag;
        let tags = tagsActuales();
        if (!tags.includes(tag)){
            tags.push(tag);
            irConTags(tags);
        }
    });
}

for (let boton of tags_seleccionados_remover){
    boton.addEventListener("click", function(){
        const tag = this.parentElement.dataset.tag;
        let tags = tagsActuales().filter(t => t !== tag);
        irConTags(tags);
    });
}

input_tag_busqueda.addEventListener("keyup", function(e){
    if (e.key === "Enter"){
        const tag = input_tag_busqueda.value.trim().toLowerCase();
        input_tag_busqueda.value = "";
        if (tag.length > 0){
            let tags = tagsActuales();
            if (!tags.includes(tag)){
                tags.push(tag);
                irConTags(tags);
            }
        }
    }
});
