const archivo = document.getElementById("archivo-file");
const image = document.getElementById("image-preview");
const imagen_texto = document.getElementById("imagen-texto");
const imagen_tamano = document.getElementById("imagen-tamano");
const imagen_res = document.getElementById("imagen-res");

const textbox = document.getElementById("titulo-input");
const textarea = document.getElementById("descripcion-input");
const enviar = document.getElementById("btn-enviar");
const mensaje_error_subir = document.getElementById("mensaje-error-subir");
const anonimo_checkbox = document.getElementById("anonimo-checkbox");
const mensaje_aviso = document.getElementById("mensaje-aviso");

// 25228792 (25MB)
let maxSize = 6228792; // 5 MB

export let req1 = false, req2 = false, req3 = false;
import {tags_introducidos} from "./tags.js";

mensaje_error_subir.style.transition = "opacity 0.3s ease";
mensaje_aviso.style.transition = "opacity 0.3s ease";
mensaje_error_subir.style.opacity = 0;
mensaje_aviso.style.opacity = 0;

archivo.addEventListener("change", (event) => {
    const imagen = event.target.files[0];
    mensaje_error_subir.style.opacity = 0;
    if (!imagen) {
        req1 = false;
        imagen_texto.textContent = "Imagen no seleccionada";
        imagen_tamano.textContent = "";
        imagen_res.textContent = "";
        image.src = "";
        enviar.disabled = true;
        return;
    }

    if (imagen.type == "image/gif"){    
        maxSize = 26228792;
    }
    console.log(imagen.type);
    imagen_texto.textContent = imagen.name;
    imagen_tamano.textContent = `${(imagen.size / 1024).toFixed(2)} KB`;
    let width, height;

    image.src = URL.createObjectURL(imagen);
    image.onload = () => {
        width = image.naturalWidth;
        height = image.naturalHeight;
        imagen_res.textContent = `${width}x${height}`;
        if (width >= 400 && height >= 300){
            req1 = true;
            console.log("1 si");
        }
        else{
            req1 = false;
            imagen_texto.textContent = "Imagen no seleccionada";
            imagen_tamano.textContent = "";
            imagen_res.textContent = "";
            image.src = "";
            
            mensaje_error_subir.innerHTML = "<span>Error al subir la imagen: </span> La resolución mínima es de 400x300";
            mensaje_error_subir.style.display = "block";
            mensaje_error_subir.style.opacity = 0;
            requestAnimationFrame(() => {
                mensaje_error_subir.style.opacity = 1; 
            });
        }

        if (req1 && req2 && req3 && textbox.value.trim() != "" && textarea.value.trim() != "") {
            enviar.disabled = false;
        } else {
            enviar.disabled = true;
        }
    };

    if (imagen.size > 10000){
        req2 = true;
        console.log("2 si");
    }
    else{
        req2 = false;
        imagen_texto.textContent = "Imagen no seleccionada";
        imagen_tamano.textContent = "";
        imagen_res.textContent = "";
        image.src = "";

        mensaje_error_subir.innerHTML = "<span>Error al subir la imagen: </span> El tamaño mínimo es de 10 KB";
        mensaje_error_subir.style.display = "block";
        mensaje_error_subir.style.opacity = 0;
        requestAnimationFrame(() => {
            mensaje_error_subir.style.opacity = 1; 
        });
    }
    if (imagen.size < maxSize) {
        req3 = true;
        console.log("3 si");
    }
    else{
        req3 = false;
        imagen_texto.textContent = "Imagen no seleccionada";
        imagen_tamano.textContent = "";
        imagen_res.textContent = "";
        image.src = "";

        if (!(imagen.type == "image/gif")){
            mensaje_error_subir.innerHTML = "<span>Error al subir la imagen: </span> El tamaño máximo es de 5 MB";
        }
        else{
            mensaje_error_subir.innerHTML = "<span>Error al subir la imagen: </span> El tamaño máximo es de 25 MB";
        }   
        mensaje_error_subir.style.display = "block";
        mensaje_error_subir.style.opacity = 0;
        requestAnimationFrame(() => {
            mensaje_error_subir.style.opacity = 1; 
        });
    }
});

textbox.addEventListener("input", (e) => {
    if (req1 && req2 && req3 && e.target.value.trim() != "" && textarea.value.trim() != "") {
        enviar.disabled = false;
    }
    else{
        enviar.disabled = true;
    }
});

textarea.addEventListener("input", (e) => {
    if (req1 && req2 && req3 && e.target.value.trim() != "" && textbox.value.trim() != "") {
        enviar.disabled = false;
    }
    else{
        enviar.disabled = true;
    }
});

anonimo_checkbox.addEventListener("change", (e) => {
    if (e.target.checked){
        mensaje_aviso.style.opacity = 0;
        mensaje_aviso.style.display = "block";
        requestAnimationFrame(() => {
            mensaje_aviso.style.opacity = 1; 
        });
    }
    else{
        mensaje_aviso.style.opacity = 0;
    }
});

mensaje_error_subir.addEventListener('transitionend', e=>{
  if (e.propertyName === "opacity" && getComputedStyle(mensaje_error_subir).opacity === "0") {
    mensaje_error_subir.style.display = "none";
  }
});

mensaje_aviso.addEventListener('transitionend', e=>{
  if (e.propertyName === 'opacity' && getComputedStyle(mensaje_aviso).opacity === "0") {
    mensaje_aviso.style.display = "none";
  }
});
