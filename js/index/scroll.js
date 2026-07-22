const galeria_imagenes = document.querySelector(".galeria-imagenes");
const sentinela = document.getElementById("galeria-cargar-mas");

if (sentinela){
    let offset = parseInt(sentinela.dataset.offset);
    let cargando = false;

    const observer = new IntersectionObserver(function(entradas){
        if (entradas[0].isIntersecting && !cargando){
            cargarMasPosts();
        }
    });

    observer.observe(sentinela);

    function cargarMasPosts(){
        cargando = true;
        sentinela.classList.add("galeria-cargar-mas-activo");

        const params = new URLSearchParams(window.location.search);
        params.set("offset", offset);

        fetch("php/post/load-posts.php?" + params.toString())
        .then(response => response.json())
        .then(data => {
            if (data.ok){
                galeria_imagenes.insertAdjacentHTML("beforeend", data.html);
                offset += 24;

                if (!data.hay_mas){
                    observer.unobserve(sentinela);
                    sentinela.remove();
                }
            }
            cargando = false;
            sentinela.classList.remove("galeria-cargar-mas-activo");
        })
        .catch(() => {
            cargando = false;
            sentinela.classList.remove("galeria-cargar-mas-activo");
        });
    }
}
