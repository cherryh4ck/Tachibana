const galeria_stats = document.getElementById("galeria-stats");

if (galeria_stats){
    fetch("api/v1/server/getStats.php")
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            galeria_stats.innerHTML = "powering <b>" + data.total_posts + "</b> posts &middot; <b>" + data.total_comentarios + "</b> comentarios &middot; <b>" + data.total_tags + "</b> tags &middot; <b>" + data.total_usuarios + "</b> usuarios";
        }
    })
    .catch(() => {});
}