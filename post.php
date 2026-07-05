<?php
    session_start();
    require "php/db/config.php";

    // para el formateo de fecha
    $año_actual = (int)date("Y");

    if (isset($_GET["id"]) && is_numeric($_GET["id"])){
        $id = $_GET["id"];
        if (!file_exists("galeria/fullsize/" . $id . ".jpg")){
            if (file_exists("galeria/fullsize/" . $id . ".gif")){
                $ext = "gif";
            }
            else{
                header("Location: error.php?id=2");
                exit();
            }
        }
        else{
            $ext = "jpg";
        }
    }
    else{
        header("Location: error.php?id=2");
        exit();
    }
    try {
        $conn = new PDO("mysql:host=$host:$puerto;dbname=$db", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = $conn->prepare("SELECT * FROM posts WHERE id = ?");
        $sql->execute([$id]);
        $fetch = $sql->fetch(PDO::FETCH_ASSOC);
        if ($fetch){
            $post_titulo = $fetch["titulo"];
            $post_id_categoria = $fetch["id_categoria"];
            $post_descripcion = $fetch["descripcion"];
            $post_id_autor = $fetch["id_autor"];
            $post_anonimo = $fetch["anonimo"];
            $post_fecha_creacion = $fetch["fecha_creacion"];
            
            // conseguir datos de la categoria
            $sql = $conn->prepare("SELECT * FROM categorias WHERE id = ?;");
            $sql->execute([$post_id_categoria]);
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);
            if ($fetch){
                $post_categoria = "/" . $fetch["nombre"] . "/";
            }
            else{
                header("Location: error.php?id=2");
                exit();
            }

            $dateTime = new DateTime($post_fecha_creacion);
            $año_post = (int)$dateTime->format('Y');
            if ($año_post == $año_actual){
                $post_fecha_creacion = $dateTime->format("d/m \a \l\a\s H:i");
            }
            else{
                $post_fecha_creacion = $dateTime->format("d/m/Y \a \l\a\s H:i");
            }

            // buscar tags del post
            $sql = $conn->prepare("SELECT * FROM posts_tags WHERE id_post = ?;");
            $sql->execute([$id]);
            $tags_fetch = $sql->fetchAll(PDO::FETCH_ASSOC);

            // buscar datos del OP
            $sql = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
            $sql->execute([$post_id_autor]);
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);
            if ($fetch){
                $post_autor_username = $fetch["username"];
                $post_autor_nickname = $fetch["nickname"];
                $post_autor_perfil = "perfil.php?id=" . $post_id_autor;
                $avatar = "resources/avatars/" . $post_id_autor . ".png";

                // buscar comentarios
                $sql = $conn->prepare("SELECT * FROM posts_comentarios WHERE id_post = ?;");
                $sql->execute([$id]);
                $fetch = $sql->fetchAll(PDO::FETCH_ASSOC);
                if ($fetch){
                    $post_comentarios = count($fetch);
                }
                else{
                    $post_comentarios = 0;
                }
            }
            else{
                header("Location: error.php?id=2");
                exit();
            }
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

    //todo:
    // hacer funcionar el botón de adjuntar imagen
    // añadir info de la imagen al adjuntar imagen
    // añadir id a los comentarios
    // añadir soporte para las imagenes en los comentarios
    // añadir categoria
    // arreglar el alineado del titulo kek
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        if ($post_anonimo == 0){
            echo "<title>$post_autor_nickname - $post_titulo</title>";
        }
        else{
            echo "<title>Anónimo - $post_titulo</title>";
        }
    ?>
    <script src="js/post/comentar.js" defer></script>
    <script src="js/post/anonimo.js" defer></script>
    <script src="js/post/adjuntar_imagen.js" defer></script>
    <script src="js/post/respuestas.js" defer></script>

    <script src="js/subir_modal.js" defer></script>

    <link rel="stylesheet" href="styles/styles.css">
    <link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
    <nav>
        <p id="nav-logo">Tachibana</p>
        <ul>
            <li><a href="index.php">Inicio</a></li>
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
        <!-- datos enviados por php -->
        <span style="display: none;" id="es_anonimo"><?=$post_anonimo?></span>
        <!-- fin -->

        <div id="preview" class="preview"></div>

        <div class="contenido-post">
            <?php
                echo "<img src='galeria/fullsize/" . $id . "." . $ext ."'>";
                echo "<div class='post-contenido'>";
                echo "<div class='post-contenido-titulo'>";
                echo "<h1 id='post-titulo'>$post_titulo</h1>";
                echo "<div class='post-contenido-tags'>";
                echo "<span id='input-tag-rojo'>$post_categoria</span>";
                if ($tags_fetch) {
                    foreach ($tags_fetch as $tag){
                        $tag_id = $tag["id_tag"];
                        $sql = $conn->prepare("SELECT * FROM tags WHERE id = ?");
                        $sql->execute([$tag_id]);
                        $tag_fetch = $sql->fetch(PDO::FETCH_ASSOC);
                        if ($tag_fetch){
                            $tag_nombre = $tag_fetch["nombre"];
                            $tag_usos = $tag_fetch["usos"];
                             echo "<a href='index.php?tags=" . urlencode($tag_nombre) . "'><span id='input-tag2'>$tag_nombre<b>$tag_usos</b></span></a>";
                        }
                    }
                }
                echo "</div>";
                echo "<div class='post-autor'>";
                if ((file_exists($avatar)) && !($post_anonimo == 1)){
                    echo "<img src='$avatar?v=" . filemtime($avatar) . "alt='' id='post-autor-avatar'>";
                }
                else{
                    echo "<img src='resources/avatar.png' alt='' id='post-autor-avatar'>";
                }
                echo "<div class='post-autor-info'>";
                echo "<div class='post-autor-info-nickname'>";
                if ($post_anonimo == 1){
                    echo "<p><b>Anónimo</b></p>";
                }
                else{
                    echo "<p><b><a href='$post_autor_perfil'>$post_autor_nickname<span id='contenido-perfil-bloque-info-username'>@$post_autor_username</span></a></b><br>";
                }
                echo "</div>";
                echo "<p>Publicado el $post_fecha_creacion</p>";
                echo "</div></div>";
                echo "<h2 id='post-titulo-descripcion'>Descripción</h2>";
                echo "<div class='post-descripcion'>";
                $post_descripcion = str_replace(["<br>", "<br />"], "</p><p>", $post_descripcion);
                $post_descripcion = "<p>$post_descripcion</p>";
                $post_descripcion = preg_replace(
                    '/<p>\s*(&gt;|>)(.*)<\/p>/',
                    '<p id="post-comentarios-greentext">&gt;$2</p>',
                    $post_descripcion
                );
                echo $post_descripcion;
                echo "</div></div>";
            ?>
            <?php
                if ($post_comentarios > 0){
                    echo "<h2 id='post-comentarios-titulo'>Comentarios ($post_comentarios)</h1>";
                }
                else{
                    echo "<h2 id='post-comentarios-titulo'>Comentarios</h1>";
                }
            ?>
            <div class="post-comentarios">
                <?php
                    if ($post_comentarios == 0){
                        echo "<p id='post-comentarios-no-comentarios'>No hay comentarios, sé el primero en comentar</p>";
                    }
                    else{
                        foreach ($fetch as $comentario){
                            $comentario_id = $comentario["id"];
                            $comentario_autor_id = $comentario["id_autor"];
                            $comentario_fecha_creacion = $comentario["fecha_creacion"];
                            $comentario_texto = $comentario["comentario"];
                            $comentario_imagen_adjuntada = $comentario["imagen_adjuntada"];
                            $comentario_op = $comentario["original_poster"];

                            $dateTime = new DateTime($comentario_fecha_creacion);
                            $año_comentario = (int)$dateTime->format('Y');
                            if ($año_comentario == $año_actual){
                                $comentario_fecha_creacion = $dateTime->format("d/m \a \l\a\s H:i");
                            }
                            else{
                                $comentario_fecha_creacion = $dateTime->format("d/m/Y \a \l\a\s H:i");
                            }
                            

                            if ($comentario_autor_id != 0){
                                $sql = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
                                $sql->execute([$comentario_autor_id]);
                                $newFetch = $sql->fetch(PDO::FETCH_ASSOC);
                            }
                            else{
                                $newFetch = [
                                    "username" => "Anónimo",
                                    "nickname" => "Anónimo"
                                ];
                            }
                            if ($newFetch){
                                $comentario_autor_username = $newFetch["username"];
                                $comentario_autor_nickname = $newFetch["nickname"];
                                if ($comentario_autor_id != 0) {
                                    $avatar = "resources/avatars/" . $comentario_autor_id . ".png";
                                    $comentario_autor_perfil = "perfil.php?id=" . $comentario_autor_id;
                                }
                                else{
                                    $avatar = "resources/avatar.png";
                                }

                                echo "<div class='post-comentarios-comentario'>";
                                echo "<div class='post-comentarios-comentario-avatar-div'>";
                                if (file_exists($avatar)){
                                    if ($comentario_autor_id != 0){
                                        echo "<a href='perfil.php?id=$comentario_autor_id'><img src='$avatar?v=" . filemtime($avatar) . "alt='' id='post-comentarios-comentario-avatar'></a>";
                                    }
                                    else{
                                        echo "<img src='$avatar' alt='' id='post-comentarios-comentario-avatar'>";
                                    }
                                }
                                else{
                                    echo "<img src='resources/avatar.png' alt='' id='post-comentarios-comentario-avatar'>";
                                }
                                echo "<p>#$comentario_id</p>";
                                echo "</div>";
                                echo "<div class='post-comentarios-comentario-info'>";
                                echo "<div class='post-comentarios-comentario-autor'>";
                                echo "<div class='post-autor-info-nickname'>";
                                if ($comentario_autor_id != 0) {
                                    echo "<p><b><a href='$comentario_autor_perfil'>$comentario_autor_nickname<span id='contenido-perfil-bloque-info-username'>@$comentario_autor_username</span></a></b></p>";
                                }
                                else{
                                    echo "<p><b>Anónimo</b></p>";
                                }

                                if ($comentario_op == 1){
                                    echo "<span id='input-tag-op'>OP</span>";
                                }
                                echo "</div>";
                                echo "<p id='post-comentarios-comentario-fecha'>$comentario_fecha_creacion</p>";
                                echo "</div>";
                                echo "<div class='post-comentarios-comentario-texto'>";
                                // es mejor hacer esto cuando se comenta pq si no, le da mucha carga al servidor
                                // aparte ayuda a no tener que hacer chequeos extras (como por ej si se referencia a un comentario que no existe todavia)
                                $comentario_texto = str_replace(["<br>", "<br />"], "</p><p>", $comentario_texto);
                                $comentario_texto = "<p>$comentario_texto</p>";

                                $lineas = explode("</p><p>", $comentario_texto);
                                $salida = "";

                                foreach ($lineas as $linea) {
                                    $linea = preg_replace("/^<p>/", "", $linea);
                                    $linea = preg_replace("/<\/p>$/", "", $linea);
                                    $contenido = trim($linea);

                                    if (preg_match("/^(?:&gt;&gt;|>>)\s*(\d+)\s*$/", $contenido, $m)) {
                                        $id_salida = (int)$m[1];
                                        $sql = $conn->prepare("SELECT * FROM posts_comentarios WHERE id = ?");
                                        $sql->execute([$id_salida]);
                                        $newFetch = $sql->fetch(PDO::FETCH_ASSOC);
                                        if (($newFetch) && !($comentario_id <= $id_salida)){
                                            if ($newFetch["id_post"] == $id){
                                                if (!($newFetch["id_autor"] == 0)){
                                                    $sql = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
                                                    $sql->execute([$newFetch["id_autor"]]);
                                                    $infoAutor = $sql->fetch(PDO::FETCH_ASSOC);
                                                }
                                                
                                                $dateTime = new DateTime($newFetch["fecha_creacion"]);
                                                $año_comentario = (int)$dateTime->format('Y');
                                                if ($año_comentario == $año_actual){
                                                    $comentario_fecha_creacion = $dateTime->format("d/m \a \l\a\s H:i");
                                                }
                                                else{
                                                    $comentario_fecha_creacion = $dateTime->format("d/m/Y \a \l\a\s H:i");
                                                }

                                                if ($newFetch["id_autor"] == 0){
                                                    $contenido = "<div class='post-preview-username'><p><b>Anónimo</b><span id='post-preview-username-fecha2'>" . $comentario_fecha_creacion . "</span></p></div><div class='post-preview-comentario'>" . str_replace(["<br>", "<br />"], "</p><p>", nl2br(htmlspecialchars($newFetch["comentario"]))) . "</div>";
                                                }
                                                else{
                                                    $contenido = "<div class='post-preview-username'><p><b>" . $infoAutor["nickname"] . "</b><span id='post-preview-username-nickname'>@" . $infoAutor["username"] . "</span><span id='post-preview-username-fecha'>" . $comentario_fecha_creacion . "</span></p></div><div class='post-preview-comentario'>" . str_replace(["<br>", "<br />"], "</p><p>", nl2br(htmlspecialchars($newFetch["comentario"]))) . "</div>";
                                                }


                                                if ($newFetch["original_poster"] == 1){
                                                    $salida .= "<p class='respuesta' id='post-comentarios-respuesta' data-id='$m[1]' data-content='" . htmlspecialchars($contenido, ENT_QUOTES) . "'>&gt;&gt;" . $m[1] . " (OP)</p>";
                                                }
                                                else{
                                                    $salida .= "<p class='respuesta' id='post-comentarios-respuesta' data-id='$m[1]' data-content='" . htmlspecialchars($contenido, ENT_QUOTES) . "'>&gt;&gt;" . $m[1] . "</p>";
                                                }
                                            }
                                            else{
                                                $salida .= "<p id='post-comentarios-respuesta-invalida'>>>Respuesta inválida</p>";
                                            }
                                        }
                                        else{
                                            $salida .= "<p id='post-comentarios-respuesta-invalida'>>>Respuesta inválida</p>";
                                        }
                                    }
                                    elseif (preg_match("/^(?:&gt;|>)(.*)$/", $contenido, $m)) {
                                        $salida .= "<p id='post-comentarios-greentext'>$contenido</p>";
                                    }
                                    else {
                                        $salida .= "<p>$contenido</p>";
                                    }
                                }
                                echo $salida;
                                if ($comentario_imagen_adjuntada == 1){
                                    $archivo_existe = file_exists("resources/posts/$id/$comentario_id.png");
                                    if ($chequeo_estricto_imagen == 1){
                                        if ($archivo_existe){
                                            echo "<img src='resources/posts/$id/$comentario_id.png' id='post-comentarios-comentario-imagen'>";
                                        }
                                    }
                                    else{
                                        echo "<img src='resources/posts/$id/$comentario_id.png' id='post-comentarios-comentario-imagen'>";
                                    }

                                    if ($archivo_existe){
                                        $imagen_tamano = filesize("resources/posts/$id/$comentario_id.png");
                                        $imagen_tamano = round($imagen_tamano / 1024, 2);
                                        echo "<p id='post-comentarios-comentario-imagen-data'>$imagen_tamano KB</p>";
                                    }
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                            }
                        }
                    }
                ?>
            </div>
            <h2 id="post-comentarios-comentar">Comentar</h2>
            <div class="post-comentarios-comentar">
                <div class="post-comentarios-comentar-seccion-avatar">
                <?php
                    if (isset($_SESSION["cuenta_id"])){
                        $avatar = "resources/avatars/" . $_SESSION["cuenta_id"] . ".png";
                        if (file_exists($avatar)){
                            echo "<img src='$avatar?v=" . filemtime($avatar) . "alt='' id='post-comentarios-comentario-avatar' class='comentar-avatar'>";
                        }
                        else{
                            echo "<img src='resources/avatar.png' alt='' id='post-comentarios-comentario-avatar' class='comentar-avatar'>";
                        }
                        if ($post_id_autor == $_SESSION["cuenta_id"]){
                            echo "<span id='input-tag-op' class='comentar-input-tag-op'>OP</span>";
                        }
                    }
                    else{
                        echo "<img src='resources/avatar.png' alt='' id='post-comentarios-comentario-avatar' class='comentar-avatar'>";
                    }
                ?>
                </div>
                <form action="php/post/comentar.php" enctype="multipart/form-data" method="POST">
                    <input type="hidden" name="id_comentario" value="<?php echo $id; ?>">
                    <input type="file" accept=".png, .jpg, .jpeg, .jfif, .webp" name="imagen" id="post-comentarios-adjuntar-imagen" style="display: none;">
                    <img src="" id="post-comentarios-imagen" style="display: none;">
                    <textarea name="comentario" id="post-comentarios-textarea" rows="2" placeholder="Tu comentario..." required <?php if(!isset($_SESSION["cuenta_id"])){echo "disabled";}?>></textarea>
                    <div class="post-comentarios-comentar-botones" <?php if(!isset($_SESSION["cuenta_id"])){echo "style='display: none;'";}?>>
                        <input type="submit" value="Comentar" id="post-comentarios-enviar" disabled>
                        <input type="button" id="post-comentarios-adjuntar-imagen-falso" value="Adjuntar imagen">
                        <div class="post-comentarios-comentar-botones-imagen-data" style="display: none;">
                            <p id="imagen-nombre-data"></p>
                            <p id="imagen-tamano-res-data">ss</p>
                        </div>
                        <div class="post-comentarios-comentar-botones-checkbox">
                            <input type="checkbox" id="anonimo-checkbox" name="anonimo">
                            <label for="anonimo">Comentar como anónimo</label>
                        </div>
                    </div>
                    <?php
                        if (!isset($_SESSION["cuenta_id"])){
                            echo "<p id='post-comentarios-texto-sesion'><a href='register.php'>Debes crear una cuenta para comentar en este post</a></p>";
                        }
                    ?>
                </form>
            </div>
            </div>
        </div>

        <?php include("resources/dialog-upload.php"); ?>
    </header>
</body>
</html>