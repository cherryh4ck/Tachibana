<?php
    require "php/db/config.php";
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
        "No se puede acceder al servidor porque está caído.<br>Intenta nuevamente en un rato.",
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
    <nav>
        <p id="nav-logo">Tachibana</p>
        <ul>
            <li><a href="index.php?pag=1">Inicio</a></li>
            <li><a href="perfiles.php">Usuarios</a></li>
        </ul>
        <div class="nav-cuenta">
            <?php
                if ($mantenimiento == 0){
                    if (!isset($_SESSION["cuenta_usuario"])){
                        echo "<a href='php/cuenta.php' id='cuenta'>Anónimo</a>"; 
                        echo "<img src='resources/avatar.png' alt=''>";
                    }
                    else{
                        echo "<a href='php/cuenta.php' id='cuenta'>" . $_SESSION["cuenta_usuario"] . "</a>"; 
                        $avatar = "resources/avatars/" . $_SESSION["cuenta_id"] . ".png";
                        if (file_exists($avatar)){
                            echo "<img src='$avatar?v=" . filemtime($avatar) . "alt=''>";
                        }
                        else{
                            echo "<img src='resources/avatar.png' alt=''>";
                        }
                    }
                }
            ?>
        </div>
    </nav>
    <header>
        <h1>Ups, hubo un problema...</h1>
        <?php
            if ($mantenimiento == 1) {
                echo "<p id='error'>" . $errores[10] . "</p>";
            } else {
                echo "<p id='error'>" . $errores[$_GET["id"] - 1] . "</p>";
            }
        ?>
        <p id="disculpas"><b>Pedimos disculpas.</b></p>
    </header>
</body>
</html>
