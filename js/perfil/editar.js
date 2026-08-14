const avatar_input = document.getElementById("avatar-file");
const avatar_preview = document.getElementById("avatar-img");
const avatar_preview2 = document.getElementById("avatar-img2");

const imagen_estirada_aviso = document.getElementById("mensaje-aviso");
const mensaje_error = document.getElementById("mensaje-error");

imagen_estirada_aviso.style.transition = "opacity 0.3s ease";
imagen_estirada_aviso.style.opacity = 0;

mensaje_error.style.transition = "opacity 0.3s ease";
mensaje_error.style.opacity = 0;

const formulario = document.getElementById("formulario-editar-perfil");
const nickname_input = document.getElementById("nickname-input");
const desc_input = document.getElementById("descripcion-input");

const submit = document.getElementById("guardar-cambios");

avatar_input.addEventListener("change", (event) => {
    const imagen = event.target.files[0];
    if (!imagen){
        return;
    }

    submit.disabled = false;
    avatar_preview.src = URL.createObjectURL(imagen);
    avatar_preview.onload = () => {
        const width = avatar_preview.naturalWidth;
        const height = avatar_preview.naturalHeight;
        avatar_preview2.src = URL.createObjectURL(imagen);

        if (width !== height) {
            imagen_estirada_aviso.style.opacity = 0;
            imagen_estirada_aviso.style.display = "block";
            requestAnimationFrame(() => {
                imagen_estirada_aviso.style.opacity = 1; 
            });
        } else {
            imagen_estirada_aviso.style.opacity = 0;
            mensaje_error.style.opacity = 0;
        }
    };
});

imagen_estirada_aviso.addEventListener('transitionend', e=>{
    if (e.propertyName === 'opacity' && getComputedStyle(imagen_estirada_aviso).opacity === "0") {
      imagen_estirada_aviso.style.display = "none";
    }
});

mensaje_error.addEventListener('transitionend', e=>{
    if (e.propertyName === 'opacity' && getComputedStyle(mensaje_error).opacity === "0") {
      mensaje_error.style.display = "none";
    }
});

formulario.addEventListener("submit", function(e) {
    let contieneEspaciosDeMas = /\s\s+/.test(nickname_input.value)
    if ((nickname_input.value.length < 3) || (nickname_input.value.length > 19)){
        e.preventDefault();
        mensaje_error.innerHTML = "<span>Error al guardar los cambios:</span> El nickname es muy corto o largo";
        mensaje_error.style.opacity = 0;
        mensaje_error.style.display = "block";
        requestAnimationFrame(() => {
            mensaje_error.style.opacity = 1; 
        });
    }
    else if (contieneEspaciosDeMas == true){
        e.preventDefault();
        mensaje_error.innerHTML = "<span>Error al guardar los cambios:</span> El nickname no puede contener espacios de más";
        mensaje_error.style.opacity = 0;
        mensaje_error.style.display = "block";
        requestAnimationFrame(() => {
            mensaje_error.style.opacity = 1; 
        });
    }
});

nickname_input.addEventListener("input", function() {
    submit.disabled = false;
});

desc_input.addEventListener("input", function() {
    submit.disabled = false;
});