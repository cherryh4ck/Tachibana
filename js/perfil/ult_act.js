const ult_vez_checkbox = document.getElementById("ultima-actividad-checkbox");
const ult_vez = document.getElementById("ultima-actividad-hidden");

ult_vez_checkbox.addEventListener("change", function(e) {
    if (ult_vez_checkbox.checked){
        ult_vez.value = 1;
    }
    else{
        ult_vez.value = 0;
    }
});

ult_vez_checkbox.addEventListener("change", function(e) {
    submit.disabled = false;
});