<?php
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Inválido."
        ]);
        exit();
    }
    

    require "../db/config.php";
    require "../../resources/parse_functions.php";
    $cuenta_obligatoria = $configuracion["general"]["cuenta_obligatoria"];
    $chequeo_estricto_imagen = $configuracion["general"]["chequeo_estricto_imagen"] ?? 1;
    $año_actual = (int)date("Y");

    if ($cuenta_obligatoria == 1 && !isset($_SESSION["cuenta_id"])){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Sin autorización."
        ]);
        exit();
    }

    if (!isset($_POST["id_comentario"]) && !isset($_POST["comentario"])) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Datos inválidos."
        ]);
        exit();
    }

    if (isset($_SESSION["cuenta_id"])) {
        if (esta_baneado($conn, $_SESSION["cuenta_id"])) {
            echo json_encode([
                "ok" => false,
                "baneado" => true,
                "mensaje" => "Baneado."
            ]);
            exit();
        }
    }

    $comentario_id = $_POST["id_comentario"];
    $comentario_autor_id = $_SESSION["cuenta_id"];
    $comentario_texto = nl2br(htmlspecialchars($_POST["comentario"]));
    $comentario_anonimo = $_POST["anonimo"];

    $imagen = $_FILES["imagen"];
    $info = pathinfo($imagen["name"]);
    if (!empty($info["extension"])){
        $comentario_imagen = 1;
    }
    else{
        $comentario_imagen = 0;
    }

    if ($comentario_anonimo == "on"){
        $comentario_anonimo = 1;
        $comentario_autor_id = 0;
    }   
    else{
        $comentario_anonimo = 0;
    }

    try{
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = $conn->prepare("SELECT * FROM posts WHERE id = ?");
        $sql->execute([$comentario_id]);
        $fetch = $sql->fetch(PDO::FETCH_ASSOC);
        if ($fetch){
            $post_autor_id = $fetch["id_autor"];
            $post_anonimo = $fetch["anonimo"];
            $baneado = $fetch["baneado"];
            $archivado = $fetch["archivado"];
            $original_poster = 0;

            if ($baneado == 1 || $archivado == 1){
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "El post está baneado o archivado."
                ]);
                exit();
            }

            if ($post_autor_id == $_SESSION["cuenta_id"]){
                if ((($comentario_anonimo == 0) && ($post_anonimo == 0)) || (($comentario_anonimo == 1) && ($post_anonimo == 1))){
                    $original_poster = 1;
                }
            }

            $sql = $conn->prepare("INSERT INTO posts_comentarios(id_post, id_autor, comentario, imagen_adjuntada, original_poster) VALUES (?, ?, ?, ?, ?);");
            $sql->execute([$comentario_id, $comentario_autor_id, $comentario_texto, $comentario_imagen, $original_poster]);
            $ultimo_insert = $conn->lastInsertId();
            if ($comentario_imagen == 1){
                if (!($info["extension"] == "png")){
                    if ($info["extension"] == "jpg" or $info["extension"] == "jpeg" or $info["extension"] == "jfif"){
                        $imagen_nueva = imagecreatefromjpeg($imagen["tmp_name"]);
                    }
                    else if ($info["extension"] == "webp") {
                        $imagen_nueva = imagecreatefromwebp($imagen["tmp_name"]);
                    }
                }
                else{
                    $imagen_nueva = imagecreatefrompng($imagen["tmp_name"]);
                }

                if (!(file_exists("../../resources/posts/$comentario_id/"))){
                    mkdir("../../resources/posts/$comentario_id/");
                }
                imagepng($imagen_nueva, "../../resources/posts/$comentario_id/$ultimo_insert.png");
            }

            if ($comentario_autor_id != 0){
                $sql = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
                $sql->execute([$comentario_autor_id]);
                $autor_fetch = $sql->fetch(PDO::FETCH_ASSOC);
            }
            else{
                $autor_fetch = [
                    "username" => "Anónimo",
                    "nickname" => "Anónimo"
                ];
            }

            $nuevo_comentario_autor_username = $autor_fetch["username"];
            $nuevo_comentario_autor_nickname = $autor_fetch["nickname"];
            $nuevo_comentario_avatar_src = "resources/avatar.png";
            if ($comentario_autor_id != 0){
                $nuevo_comentario_autor_perfil = "perfil.php?id=" . $comentario_autor_id;
                $avatar = "../../resources/avatars/" . $comentario_autor_id . ".png";
                if (file_exists($avatar)) {
                    $nuevo_comentario_avatar_src = $avatar;
                }
            }

            $fecha_actual = new DateTime();
            $año_comentario = (int)$fecha_actual->format('Y');
            if ($año_comentario == $año_actual){
                $nuevo_comentario_fecha = $fecha_actual->format("d/m \a \l\a\s H:i");
            }
            else{
                $nuevo_comentario_fecha = $fecha_actual->format("d/m/Y \a \l\a\s H:i");
            }

            $nuevo_comentario_texto_html = parsear_comentario_texto($comentario_texto, $conn, $comentario_id, $ultimo_insert, $año_actual, $chequeo_estricto_imagen);

            $imagen_html = "";
            if ($comentario_imagen == 1){
                $ruta_imagen = "resources/posts/$comentario_id/$ultimo_insert.png";
                $archivo_existe = file_exists("../../$ruta_imagen");
                if (!$chequeo_estricto_imagen || $archivo_existe){
                    $imagen_html .= "<img src='$ruta_imagen' id='post-comentarios-comentario-imagen'>";
                }
                if ($archivo_existe){
                    $imagen_tamano = round(filesize("../../$ruta_imagen") / 1024, 2);
                    $imagen_html .= "<p id='post-comentarios-comentario-imagen-data'>$imagen_tamano KB</p>";
                }
            }

            $html = "<div class='post-comentarios-comentario'>";
            $html .= "<div class='post-comentarios-comentario-avatar-div'>";
            if ($comentario_autor_id != 0){
                $html .= "<a href='$nuevo_comentario_autor_perfil'><img src='$nuevo_comentario_avatar_src' alt='' id='post-comentarios-comentario-avatar'></a>";
            }
            else{
                $html .= "<img src='$nuevo_comentario_avatar_src' alt='' id='post-comentarios-comentario-avatar'>";
            }
            $html .= "<p>#$ultimo_insert</p>";
            $html .= "</div>";
            $html .= "<div class='post-comentarios-comentario-info'>";
            $html .= "<div class='post-comentarios-comentario-autor'>";
            $html .= "<div class='post-autor-info-nickname'>";
            if ($comentario_autor_id != 0){
                $html .= "<p><b><a href='$nuevo_comentario_autor_perfil'>$nuevo_comentario_autor_nickname<span id='contenido-perfil-bloque-info-username'>@$nuevo_comentario_autor_username</span></a></b></p>";
            }
            else{
                $html .= "<p><b>Anónimo</b></p>";
            }
            if ($original_poster == 1){
                $html .= "<span id='input-tag-op'>OP</span>";
            }
            $html .= "</div>";
            $html .= "<p id='post-comentarios-comentario-fecha'>$nuevo_comentario_fecha</p>";
            $html .= "</div>";
            $html .= "<div class='post-comentarios-comentario-texto'>";
            $html .= $nuevo_comentario_texto_html;
            $html .= $imagen_html;
            $html .= "</div>";
            $html .= "</div>";
            $html .= "</div>";
        }
    }
    catch (PDOException $e){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Hubo un error."
        ]);
        exit();
    }

    echo json_encode([
        "ok" => true,
        "mensaje" => "Comentado.",
        "html" => $html
    ]);
    exit();
?>