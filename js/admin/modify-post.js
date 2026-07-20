const sticky_boton = document.getElementById("post-admin-fijar");
const post_titulo_fijado = document.getElementById("post-titulo-fijado");
var accion = "";

sticky_boton.addEventListener("click", function(e){
    e.preventDefault();
    accion = "sticky";
    var postId = sticky_boton.dataset.id;

    sticky_boton.disabled = true;

    fetch("php/admin/modify-post.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: accion, post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            console.log("asdasdasd!!!");
            if (data.value == 1){
                post_titulo_fijado.style.display = "inline";
            }
            else{
                post_titulo_fijado.style.display = "none";
            }
        }
        else {
            console.log("malito");
        }
        sticky_boton.disabled = false;
    })
    .catch(() => {
        sticky_boton.disabled = false;
    });
});