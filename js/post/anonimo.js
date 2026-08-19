const anonimo_checkbox = document.getElementById("anonimo-checkbox");
const avatar = document.getElementsByClassName("comentar-avatar")[0];
const comentar_op = document.getElementsByClassName("comentar-input-tag-op-script")[0];
const es_anonimo = parseInt(document.getElementById("es_anonimo").textContent);

const formulario_comentar_2 = document.getElementById("formulario-comentar");

let avatar_actual = "";

if (comentar_op != null && es_anonimo == 1){
    comentar_op.style.opacity = 0.5;
    comentar_op.style.transition = "opacity 0.5s ease";
}

if (avatar.src.endsWith("resources/avatar.png") == false && avatar_actual == ""){
    avatar_actual = avatar.src;
}
else if (avatar.src.endsWith("resources/avatar.png") == true ){
    avatar_actual = "resources/avatar.png";
}

function chequearAnonimato() {
    if (anonimo_checkbox.checked){
        avatar.src = "resources/avatar.png";
        if (comentar_op != null && es_anonimo == 1){
            comentar_op.style.opacity = 1;
        }
        else if (comentar_op != null && es_anonimo == 0){
            comentar_op.style.opacity = 0.5;
        }
    }
    else{
        avatar.src = avatar_actual;
        if (comentar_op != null && es_anonimo == 1){
            comentar_op.style.opacity = 0.5;
        }
        else if (comentar_op != null && es_anonimo == 0){
            comentar_op.style.opacity = 1;
        }
    }
}

formulario_comentar_2.addEventListener("reset", function(e) {
    queueMicrotask(() => {
        chequearAnonimato();
    });
});

anonimo_checkbox.addEventListener("change", function(e) {
    chequearAnonimato();
});
