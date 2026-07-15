<?php
    require "php/db/config.php";
    require "resources/parse_functions.php";
    if ($mantenimiento == 0 && $conn_test == 1){
        session_start();
    }

    // No empieza en 0, sino en 1
    $errores = [
        "No hay contenido en esta página.",
        "No se ha encontrado el contenido solicitado.",
        "La imagen está corrupta o no es válida.",
        "No se ha encontrado el contenido solicitado porque fue eliminado por el usuario.",
        "No se ha encontrado el contenido solicitado porque fue moderado.",
        "No se ha encontrado la cuenta solicitada.", 
        "La imagen debe tener una resolución mínima de 300x300 píxeles.", // sin usar
        "El tamaño de la imagen debe ser menor a 5.2 MBs.", // sin usar
        "El servidor se encuentra temporalmente caído.<br>Intenta nuevamente en un rato.",
        "No se puede acceder al servidor porque está en mantenimiento.<br>En breve, la página mostrará cuando se acaba el mantenimiento.",
    ];
    if ((!isset($_GET["id"])) || (!is_numeric($_GET["id"])) || ($_GET["id"] < 1) || ($_GET["id"] > count($errores)) && (!$mantenimiento)) {
        header("Location: index.php?pag=1");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="styles/styles.css">
    <script src="js/archivos.js" defer></script>
    <link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
    <?php include("resources/nav.php"); ?>
    <header class="error-header">
        <div class="error-card">
            <p class="error-icono">&#9888;</p>
            <h1>Ups, hubo un problema...</h1>
            <?php
                if ($mantenimiento == 1) {
                    echo "<p id='error'>" . $errores[9] . "</p>";
                } else {
                    echo "<p id='error'>" . $errores[$_GET["id"] - 1] . "</p>";
                }
            ?>
            <p id="disculpas"><b>Pedimos disculpas.</b></p>
            <a href="index.php?pag=1" class="error-volver">Volver al inicio</a>
        </div>
    </header>
</body>
</html>
