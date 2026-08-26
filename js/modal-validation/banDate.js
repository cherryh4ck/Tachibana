const date_formulario = document.getElementById("ban-fecha-expiracion");

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