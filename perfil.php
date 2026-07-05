<?php
    session_start();
    
    require "php/db/config.php";
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $modo = "ver";
    if (isset($_GET["id"])){
        if (!is_numeric($_GET["id"])){
            header("Location: error.php?id=2");
            exit();
        }
    }
    else{
        if (isset($_SESSION["cuenta_usuario"])){
            $_GET["id"] = $_SESSION["cuenta_id"];
        }
        else{
            header("Location: login.php");
            exit();
        }
    }

    if (isset($_GET["editar"])){
        $modo = "editar";
    }
    else if(isset($_GET["seguridad"])){
        $modo = "seguridad";
    }

    try{
        $sql = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $sql->execute([$_GET["id"]]);
        $fetch = $sql->fetch(PDO::FETCH_ASSOC);
        if ($fetch){
            $nombre_usuario = $fetch["username"];
            $nickname = $fetch["nickname"];
            $descripcion = $fetch["descripcion"];
            $rol = $fetch["rol"];
            $fecha_creacion = $fetch["fecha_creacion"];
            $ultima_actividad = $fetch["ult_act"];
            $ultima_actividad_activo = $fetch["ult_act_activo"];
            $avatar = "resources/avatars/" . $_GET["id"] . ".png";
        }
        else{
            header("Location: error.php?id=2");
            exit();
        }
    }
    catch (PDOException $e){
        header("Location: error.php?id=2");
        exit();
    }

    function calcular_tiempo($fecha) {
        // gracias chatgpt, ni sabía de esto kek
        $ahora = new DateTime();
        $fecha_obj = new DateTime($fecha);
        $diferencia = $ahora->diff($fecha_obj);

        if ($diferencia->y > 0) {
            return $diferencia->y . " año" . ($diferencia->y > 1 ? "s" : "");
        } elseif ($diferencia->m > 0) {
            return $diferencia->m . " mes" . ($diferencia->m > 1 ? "es" : "");
        } elseif ($diferencia->d > 0) {
            return $diferencia->d . " día" . ($diferencia->d > 1 ? "s" : "");
        } elseif ($diferencia->h > 0) {
            return $diferencia->h . " hora" . ($diferencia->h > 1 ? "s" : "");
        } elseif ($diferencia->i > 0) {
            return $diferencia->i . " minuto" . ($diferencia->i > 1 ? "s" : "");
        } else {
            if ($diferencia->s < 1) {
                $diferencia->s = 1;
            }
            return $diferencia->s . " segundo" . ($diferencia->s > 1 ? "s" : "");
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        echo "<title> $nickname - @$nombre_usuario </title>";
    ?>
    <script src="js/subir_modal.js" defer></script>

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
    <header>
        <div class="perfil-div">
            <div class="perfil-banner">
                <?php
                    if ($modo == "ver"){
                        echo "<div class='perfil-banner-parte1'>";
                        if (file_exists($avatar)){
                            echo "<img src='$avatar?v=" . filemtime($avatar) . "alt=''>";
                        }
                        else{
                            echo "<img src='resources/avatar.png' alt=''>";
                        }
                        echo "<div class='perfil-info'>";
                        echo "<div class='perfil-info-nickname-tags'>";
                        echo "<p><b>$nickname</p></b>";
                        if ($rol == "admin" || $rol == "mod"){
                            echo "<span id='input-tag-" . $rol . "'class='comentar-input-tag-op'>" . strtoupper($rol) . "</span>";
                        }
                        echo "</div>";
                        echo "<p id='contenido-perfil-bloque-info-username'>@$nombre_usuario</p>";
                        echo "<div class='perfil-info-avanzada'>";

                        $se_unio = "Se unió hace " . calcular_tiempo($fecha_creacion);
                        $ult_vez = "Última vez hace " . calcular_tiempo($ultima_actividad);

                        if ($ultima_actividad_activo == 1){
                            echo "<p>$se_unio <span id='viñeta'>•</span> $ult_vez</p>";
                        }
                        else{
                            echo "<p>$se_unio</p>";
                        }
                        echo "</div>";  
                        echo "</div>";
                        echo "</div>";
                        if (isset($_SESSION["cuenta_id"])){
                            if ($_GET["id"] == $_SESSION["cuenta_id"]){
                                echo "<div class='perfil-banner-parte2'>";
                                echo '<button onclick="window.location.href=\'perfil.php?editar=1\'">Editar perfil</button>';
                                echo '<button onclick="window.location.href=\'perfil.php?seguridad=1\'">Administrar seguridad</button>';
                                echo '<button onclick="window.location.href=\'php/db/logout.php\'" id="boton-cerrar-sesion">Cerrar sesión</button>';
                                echo "</div>";
                            }
                        }
                    }
                    else{
                        if (isset($_SESSION["cuenta_id"])){
                            if ($_GET["id"] == $_SESSION["cuenta_id"]){
                                
                            }
                            else{
                                header("Location: index.php");
                            }
                        }
                    }
                ?>
            </div>
        </div>
        <?php
            if ($modo == "ver"){
                echo "<div class='perfil-div perfil-div-separacion'>";
                echo "<div class='perfil-descripcion'>";
                echo "<p id='perfil-descripcion-texto'>Descripción</p>";
                if (!empty($descripcion)){
                    $descripcion = str_replace(["<br>", "<br />"], "</p><p>", $descripcion);
                    $descripcion = "<p>$descripcion</p>";
                    $descripcion = preg_replace(
                        '/<p>\s*(&gt;|>)(.*)<\/p>/',
                        '<p id="post-comentarios-greentext">&gt;$2</p>',
                        $descripcion
                    );
                    echo $descripcion;
                }
                else{
                    echo "<p>No hay descripción.</p>";
                }
                echo "</div>";
            }
            else if ($modo == "editar"){
                echo <<<EOM
                <div class="perfil-div">
                <div class="perfil-banner">
                <div class="perfil-banner-parte1-modificado">
                <script src="js/perfil/editar.js" defer></script>
                <script src="js/perfil/ult_act.js" defer></script>
                <script src="js/perfil/caracteres.js" defer></script>
                <form action="php/account/editar.php" method="POST" enctype="multipart/form-data" id='formulario-editar-perfil' onkeydown="if (event.keyCode === 13 && event.target.tagName !== 'TEXTAREA') {return false;}">
                EOM;
                if ($ultima_actividad_activo == 1){
                    echo "<input type='hidden' name='ultima-actividad' id='ultima-actividad-hidden' value='1'>";
                }
                else{
                    echo "<input type='hidden' name='ultima-actividad' id='ultima-actividad-hidden' value='0'>";
                }
                echo <<<EOM
                <div class="perfil-banner-parte1-fila">
                <div class="perfil-banner-parte1-modificado-input">
                <p>Nickname</p>
                EOM;
                echo "<input type='text' name='nickname' id='nickname-input' value='$nickname' placeholder='Nickname...'>";
                echo <<<EOM
                </div>
                <div class="perfil-banner-parte1-modificado-input perfil-banner-parte1-modificado-input-username">
                <p>Username</p>
                EOM;
                echo "<input type='text' value='$nombre_usuario' disabled>";
                echo <<<EOM
                </div>
                </div>
                <div class="perfil-banner-parte1-modificado-input">
                <p>Descripción</p>
                EOM;
                echo "<textarea name='descripcion' id='descripcion-input' class='descripcion-input-perfil' placeholder='Descripción...' max-length='400'>" . strip_tags($descripcion) . "</textarea>";
                echo "<p style='display: none;' id='perfil-banner-parte1-modificado-input-caracteres'>ad</p>";
                echo <<<EOM
                </div>
                <div class="perfil-banner-parte1-modificado-input">
                <p>Avatar</p>
                <div class="perfil-banner-parte1-modificado-input-avatar">
                <div class="perfil-banner-parte1-modificado-input-avatar-preview">
                EOM;
                if (file_exists($avatar)){
                    echo "<img src='$avatar?v=" . filemtime($avatar) . "alt='' id='avatar-img'>";
                    echo "<p>80px</p>";
                    echo "</div>";
                    echo "<div class='perfil-banner-parte1-modificado-input-avatar-preview'>";
                    echo "<img src='$avatar?v=" . filemtime($avatar) . "alt='' id='avatar-img2' class='perfil-banner-parte1-modificado-input-avatar-preview-chiquito'>";
                    echo "<p>50px</p>";
                    echo "</div>";
                }
                else{
                    echo "<img src='resources/avatar.png' alt='' id='avatar-img'>";
                    echo "<p>80px</p>";
                    echo "</div>";
                    echo "<div class='perfil-banner-parte1-modificado-input-avatar-preview'>";
                    echo "<img src='resources/avatar.png' alt='' id='avatar-img2' class='perfil-banner-parte1-modificado-input-avatar-preview-chiquito'>";
                    echo "<p>50px</p>";
                    echo "</div>";
                }
                echo <<<EOM
                <input type="file" accept=".png, .jpg, .jpeg" name="avatar" id="avatar-file">
                </div>
                </div>
                <div class="perfil-banner-parte1-modificado-input">
                    <p>Privacidad</p>
                </div>
                <div class="perfil-banner-parte1-checkbox">
                EOM;
                if ($ultima_actividad_activo == 1){
                    echo "<input type='checkbox' id='ultima-actividad-checkbox' checked>";
                }
                else{
                    echo "<input type='checkbox' id='ultima-actividad-checkbox'>";
                }
                echo <<<EOM
                    <label for="anonimo">Mostrar última actividad</label>
                </div>   
                <div class="contenido-subir-formulario-error perfil-editar-mensaje">
                    <!-- div para mostrar errores / avisos mediante js/archivos.js -->
                    <p style="display: none;" id="mensaje-error"><span>Error al editar el perfil:</span> Test test</p>
                    <p style="display: none;" id="mensaje-aviso"><span id="mensaje-aviso2">Aviso:</span> El ancho y la altura del avatar no coinciden, por lo que puede verse estirado.</p>
                </div>
                <div class="perfil-banner-parte1-modificado-input perfil-banner-parte1-modificado-input-gap">
                <input type="submit" value="Guardar cambios" id="guardar-cambios" disabled>
                <input type="button" value="Volver" onclick="window.location.href='perfil.php'">
                </div>
                </form>
                </div>
                </div>
                </div>
                EOM;
            }
            else{
                echo <<<EOM
                <div class="perfil-div">
                <div class="perfil-banner">
                <div class="perfil-banner-parte1-modificado">
                <script src="js/perfil/seguridad.js" defer></script>
                <form action="php/account/seguridad.php" method="POST" id='formulario-seguridad-perfil' onkeydown="if (event.keyCode === 13 && event.target.tagName !== 'TEXTAREA') {return false;}">
                <div class="perfil-banner-parte1-modificado-input">
                <p>Contraseña actual</p>
                EOM;
                echo "<input type='password' name='actual' id='actual-input' placeholder='Contraseña actual...'>";
                echo <<<EOM
                </div>
                <div class="perfil-banner-parte1-modificado-input">
                <p>Contraseña nueva</p>
                EOM;
                echo "<input type='password' name='nueva' id='nueva-input' placeholder='Contraseña nueva...'>";
                echo <<<EOM
                </div>
                <div class="perfil-banner-parte1-modificado-input">
                <p>Repetir contraseña nueva</p>
                EOM;
                echo "<input type='password' name='repetir' id='repetir-input' placeholder='Repetir contraseña nueva...'>";
                echo <<<EOM
                </div>
                <div class="contenido-subir-formulario-error perfil-editar-mensaje">
                        <p style="display: none;" id="mensaje-error"><span>Error al cambiar la contraseña:</span> Test test</p>
                        <p style="display: none;" id="mensaje-aviso"><span id="mensaje-aviso2">Aviso:</span> La contraseña se cambió correctamente.</p>
                </div>
                EOM;
                if (isset($_GET["error"])){
                    $errores_seguridad = [
                        1 => "Los campos están vacíos",
                        2 => "Las contraseñas nuevas no coinciden",
                        3 => "La contraseña nueva debe tener entre 6 y 72 caracteres",
                        4 => "La contraseña actual es incorrecta",
                    ];
                    if (isset($errores_seguridad[$_GET["error"]])){
                        echo "<script>window.addEventListener('DOMContentLoaded', () => { mostrarErrorSeguridad('" . $errores_seguridad[$_GET["error"]] . "'); });</script>";
                    }
                }
                if (isset($_GET["ok"])){
                    echo "<script>window.addEventListener('DOMContentLoaded', () => { mostrarAvisoSeguridad(); });</script>";
                }
                echo <<<EOM
                <div class="perfil-banner-parte1-modificado-input perfil-banner-parte1-modificado-input-gap">
                <input type="submit" value="Cambiar contraseña" id="guardar-cambios-seguridad">
                <input type="button" value="Volver" onclick="window.location.href='perfil.php'">
                </div>
                </form>
                </div>
                </div>
                </div>
                EOM;
            }
        ?>

        <?php include("resources/dialog-upload.php"); ?>
    </header>
</body>
</html>
