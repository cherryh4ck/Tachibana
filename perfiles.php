<?php
    session_start();
    require "php/db/config.php";

    if (isset($_GET["q"])){
        if ((strlen($_GET["q"]) > 2) && !(empty($_GET["q"]))){
            $query = "%" . $_GET["q"] . "%";
            try{
                $conn = new PDO("mysql:host=$host:$puerto;dbname=$db", $user, $pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = $conn->prepare("SELECT * FROM usuarios WHERE lower(username) LIKE ?");
                $sql->execute([$query]);
                $fetch = $sql->fetchAll(PDO::FETCH_ASSOC);
            }
            catch(PDOException $e){
                header("Location: error.php?id=9");
                exit();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <script src="js/subir_modal.js" defer></script>
    <script src="js/perfiles/q_check.js" defer></script>

    <link rel="stylesheet" href="styles/styles.css">
    <link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
    <nav>
        <p id="nav-logo">Tachibana</p>
        <ul>
            <li><a href="index.php?pag=1">Inicio</a></li>
            <?php
                if (isset($_SESSION["cuenta_usuario"])){
                    echo "<li><a href='#' id='subir-boton-modal'>Publicar</a></li>";
                }
            ?>
            <li><a href="perfiles.php">Usuarios</a></li>
        </ul>
        <div class="nav-cuenta">
            <?php
                if (!isset($_SESSION["cuenta_usuario"])){
                    echo "<a href='php/cuenta.php' id='cuenta'>Anónimo</a>"; 
                    echo '<img src="resources/avatar.png" alt="">';
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
            ?>
        </div>
    </nav>
    <div class="contenido-perfiles">
        <p>Buscar un usuario</p>
        <form action="perfiles.php" method="GET" id="formulario-buscar-usuario">
            <input type="text" name="q" placeholder="Introducir el nombre de usuario..." id="nombreUsuario">
        </form>
    </div>
    <div class="contenido-perfiles-usuarios">
        <?php
            if (isset($fetch)){
                echo "<p>Usuarios</p>";
                if (count($fetch) == 0){
                    echo "<p id='no-se-ha-encontrado'>No se han encontrado usuarios.</p>";
                }
                else{
                    echo "<div class='contenido-perfiles-usuarios-lista'>";
                    foreach ($fetch as $usuario){
                        $avatar = "resources/avatars/" . $usuario["id"] . ".png";
                        echo "<div class='contenido-perfil-bloque' onclick=\"location.href='perfil.php?id=" . $usuario["id"] . "'\">";
                        if (file_exists($avatar)){
                            echo "<img src='$avatar?v=" . filemtime($avatar) . "alt=''>";
                        }
                        else{
                            echo "<img src='resources/avatar.png' alt=''>";
                        }
                        echo "<div class='contenido-perfil-bloque-info'>";
                        echo "<div class='perfil-info-nickname-tags'>";
                        echo "<p><b>" . $usuario["nickname"] . "</b></p>";
                        if ($usuario["rol"] == "admin"){
                            echo "<span id='input-tag-admin' class='comentar-input-tag-op'>ADMIN</span>";
                        }
                        echo "</div>";
                        echo "<p id='contenido-perfil-bloque-info-username'>@" . htmlspecialchars($usuario["username"]) . "</p>";
                        if (!empty($usuario["descripcion"])){
                            echo "<p>" . strip_tags($usuario["descripcion"]) . "</p>";
                        }
                        else{
                            echo "<p>No hay descripción.</p>";
                        }
                        echo "</div></div>";
                    }
                    echo "</div>";
                }
            }
        ?>
        </div>
    </div>

    <?php include("resources/dialog-upload.php"); ?>
</body>
</html>
