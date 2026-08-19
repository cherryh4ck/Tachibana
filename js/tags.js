const caracteres_permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_ ";
let tag_permitido = true;
let tags = [];

export let tags_introducidos = 0;
import {req1, req2, req3} from "./archivos.js";

const insert_tags = document.getElementById("insert-tags");
const tags_input = document.getElementById("tags-input");
const titulo_input = document.getElementById("titulo-input")
const textarea = document.getElementById("descripcion-input");
const enviar = document.getElementById("btn-enviar");
const mensaje_error = document.getElementById("mensaje-error-subir");
const tagsValue = document.getElementById("tags-value");
let no_tags = document.getElementById("no-hay-tags");

const formulario = document.getElementById("formulario-subir");

function introducirTag(tag){
    tag = tag.trim()
    tag_permitido = true;
    if ((tag.length <= 20) && (tags_introducidos < 4) && (tag != "") && (/\S/.test(tag) && (tag != null) && (tag.length >= 2) && (!tags.includes(tag)))){
        for (let i = 0; i < tag.length; i++) {
            if (caracteres_permitidos.includes(tag[i]) == false) {
                tag_permitido = false;
                break;
            }
        }
        if (tag_permitido){
            tags_introducidos++;
            no_tags = document.getElementById("no-hay-tags");
            if (no_tags != null) {
                no_tags.remove();
            }
            tags.push(tag)

            mensaje_error.style.opacity = 0;

            let span = document.createElement("span");
            span.className = "input-tag";
            span.id = "input-tag";
            span.innerText = tag;

            let botonRemover = document.createElement("input");
            botonRemover.id = "remover-tag";
            botonRemover.type = "button";
            botonRemover.value = "x";
            botonRemover.addEventListener("click", function () {
                tags_introducidos--;
                tags = tags.filter(t => t !== tag);
                span.remove();
                if (tags_introducidos == 0) {
                    no_tags = document.createElement("p");
                    no_tags.id = "no-hay-tags";
                    no_tags.innerText = "Añade un tag...";
                    insert_tags.appendChild(no_tags);
                }
            });

            span.appendChild(botonRemover);

            insert_tags.appendChild(span);
            if (req1 && req2 && req3 && titulo_input.value.trim() != "" && textarea.value.trim() != ""){
                enviar.disabled = false;
            }
            else{
                enviar.disabled = true;
            }
        }
        else{
            mensaje_error.innerHTML = "<span>Error al añadir tag: </span> El tag contiene caracteres ilegales";
            mensaje_error.style.display = "block";
            mensaje_error.style.opacity = 0;
            requestAnimationFrame(() => {
                mensaje_error.style.opacity = 1; 
            });
        }
    }
    else{
        if (tag.length > 20){
            mensaje_error.innerHTML = "<span>Error al añadir tag: </span> El tag supera los 20 caracteres";
        }
        else if (tags_introducidos >= 4){
            mensaje_error.innerHTML = "<span>Error al añadir tag: </span> El máximo de tags es de 4";
        }
        else if (tag.length < 2){
            mensaje_error.innerHTML = "<span>Error al añadir tag: </span> El tag debe tener al menos 2 caracteres";
        }
        else{
            mensaje_error.innerHTML = "<span>Error al añadir tag: </span> El tag no puede estar vacío";
        }
        mensaje_error.style.display = "block";
        mensaje_error.style.opacity = 0;
        requestAnimationFrame(() => {
            mensaje_error.style.opacity = 1; 
        });
    }
}

tags_input.addEventListener('keyup', function (e) {
    if (e.key === 'Enter') {
        introducirTag(tags_input.value.toLowerCase());
        tags_input.value = "";
    }
});

formulario.addEventListener("submit", function (e) {
    tagsValue.value = tags.join(",");
});
