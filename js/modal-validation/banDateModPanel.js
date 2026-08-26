// Honestamente no hace falta chequear del lado del servidor (creo yo)
// no genera mucha molestia, aparte es una función privada
const date_formulario = document.getElementById("modpanel-ban-usuarios-expiracion");

function validDate(value) {
  const date = new Date(value);
  const now = new Date();

  if (isNaN(date.getTime())) {
    return false;
  }

  return date >= now;
}

date_formulario.addEventListener("change", function(e){
    if (!validDate(date_formulario.value)) {
        notify("No es una fecha válida.", "error");
        date_formulario.value = "";
    }
});