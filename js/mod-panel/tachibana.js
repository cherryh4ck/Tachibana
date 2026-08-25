function loadTachibanaData() {
    const tachibanaVersion = document.getElementById("modpanel-tachibana-version");
    fetch("api/v1/server/getVersion.php")
    .then(response => response.json())
    .then(data => {
        if (data.ok){
            tachibanaVersion.innerHTML = "<b>Tachibana</b> " + data.version; 
        }
    })
    .catch(() => {});
}

loadTachibanaData();