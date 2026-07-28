const formulario = document.getElementById("formulario");
const username = document.getElementById("usernameF");
const contraseña = document.getElementById("contraseña");
const repetirContraseña = document.getElementById("repetirContraseña");
const divMensaje = document.getElementById("register-mensaje");

const regex = /^(?!.*_{2,})[a-zA-Z0-9_]{3,16}$/;

const mensaje = document.createElement("p");
mensaje.id = "formulario-mensaje";
mensaje.innerHTML = "<span>Error:</span> El usuario ya existe.";

mensaje.style.transition = "opacity 0.3s ease";
mensaje.style.opacity = 0;

let error = false;

formulario.addEventListener("submit", function(event) {
    divMensaje.innerHTML = "";

    if (contraseña.value !== repetirContraseña.value){
        event.preventDefault();
        mensaje.innerHTML = "<span>Error:</span> Las contraseñas no coinciden.";
        error = true;
    }
    else{
        if (contraseña.value == username.value){
            event.preventDefault();
            mensaje.innerHTML = "<span>Error:</span> La contraseña no puede ser igual al usuario.";
            error = true;
        }
        else{
            if (!regex.test(username.value)){
                event.preventDefault();
                mensaje.innerHTML = "<span>Error:</span> El usuario es muy corto, muy largo o contiene caracteres inválidos.";
                error = true;
            }
            else{
                if (contraseña.value.length < 6){
                    event.preventDefault();
                    mensaje.innerHTML = "<span>Error:</span> La contraseña debe tener al menos 6 caracteres.";
                    error = true;
                }
                else{
                    mensaje.innerHTML = "Creando cuenta...";
                }
            }
        }
    }

    divMensaje.append(mensaje);
    if (error == true){
        mensaje.style.opacity = 0;
        mensaje.style.display = "block";
        requestAnimationFrame(() => {
            mensaje.style.opacity = 1; 
        });
    }
});

mensaje.addEventListener('transitionend', e=>{
    if (e.propertyName === 'opacity' && getComputedStyle(mensaje).opacity === "0") {
      mensaje.style.display = "none";
    }
});
