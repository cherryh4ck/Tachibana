<?php
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] != "POST"){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Inválido."
        ]);
        exit();
    }

    require "db/config.php";
    require "../resources/parse_functions.php";

    if ($conn_test == 0){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Sin conexión a la base de datos."
        ]);
        exit();
    }

    $cuenta_obligatoria = $configuracion["general"]["cuenta_obligatoria"];

    if ($cuenta_obligatoria == 1 && !isset($_SESSION["cuenta_id"])){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Sin autorización."
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

    $maxSize = 6228792; // esta variable es para el tamaño maximo del archivo, se cambia si el archivo es de tipo gif

    // datos de entrada
    $post_titulo = htmlspecialchars($_POST["titulo"]);
    $post_categoria = $_POST["categoria"];
    $post_descripcion = nl2br(htmlspecialchars($_POST["descripcion"]));
    $post_autor_id = $_SESSION["cuenta_id"] ?? null;
    $post_anonimo = $_POST["anonimo"];
    if ($post_anonimo == "on"){
        $post_anonimo = 1;
    }
    else{
        $post_anonimo = 0;
    }
    $post_tags = $_POST["tags"] ?? "";
    $archivo = $_FILES["archivo"] ?? null;

    if (!isset($archivo) || $archivo["error"] != UPLOAD_ERR_OK || !is_uploaded_file($archivo["tmp_name"])){ // chequear si de entrada tenemos un archivo válido
        echo json_encode([
            "ok" => false,
            "mensaje" => "Archivo inválido."
        ]);
        exit();
    }

    if (!is_numeric($post_categoria)){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Categoría inválida."
        ]);
        exit();
    }

    try{
        $sql = $conn->prepare("SELECT id FROM categorias WHERE id = ?");
        $sql->execute([$post_categoria]);
        if (!$sql->fetch(PDO::FETCH_ASSOC)){
            echo json_encode([
                "ok" => false,
                "mensaje" => "Categoría no encontrada."
            ]);
            exit();
        }
    }
    catch (PDOException $e){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al hacer la query en la base de datos."
        ]);
        exit();
    }

    $dir = realpath(__DIR__ . "/../galeria") . DIRECTORY_SEPARATOR;
    $fullsize = realpath(__DIR__ . "/../galeria/fullsize") . DIRECTORY_SEPARATOR;

    $tamaño = filesize($archivo["tmp_name"]);
    $imagesize = getimagesize($archivo["tmp_name"]);

    if (!$imagesize){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Tipo de imagen no válido."
        ]);
        exit();
    }

    list($x, $y) = $imagesize;
    $tipo_real = $imagesize[2];

    if ($x < 400 or $y < 300){
        header("Location: ../error.php?id=7");
        exit();
    }

    if ($tipo_real == IMAGETYPE_GIF){
        $maxSize = 26228792;
    }

    if (!($tamaño < $maxSize)){
        header("Location: ../error.php?id=8");
        exit();
    }

    if ($tipo_real == IMAGETYPE_PNG){
        $imagen = imagecreatefrompng($archivo["tmp_name"]);
        $extension_real = "png";
    }
    else if ($tipo_real == IMAGETYPE_JPEG){
        $imagen = imagecreatefromjpeg($archivo["tmp_name"]);
        $extension_real = "jpg";
    }
    else if ($tipo_real == IMAGETYPE_WEBP){
        $imagen = imagecreatefromwebp($archivo["tmp_name"]);
        $extension_real = "webp";
    }
    else if ($tipo_real == IMAGETYPE_GIF){
        $imagen = imagecreatefromgif($archivo["tmp_name"]);
        $extension_real = "gif";
    }
    else{
        $imagen = false;
    }

    if (!$imagen){
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al procesar la imagen."
        ]);
        exit();
    }

    try{
        $conn->beginTransaction();

        $sql = $conn->prepare("INSERT INTO posts(id_autor, id_categoria, titulo, descripcion, anonimo) VALUES (?, ?, ?, ?, ?);");
        $sql->execute([$post_autor_id, $post_categoria, $post_titulo, $post_descripcion, $post_anonimo]);

        $last_insert = $conn->lastInsertId();

        if (!(empty($post_tags))){
            $tags = explode(",", $post_tags);
            foreach ($tags as $tag){
                $tag = strtolower(trim($tag));
                if (empty($tag) || strlen($tag) > 20 || strlen($tag) < 2){
                    continue;
                }

                $sql = $conn->prepare("SELECT * from tags WHERE nombre = ?");
                $sql->execute([$tag]);
                $fetch = $sql->fetch(PDO::FETCH_ASSOC);
                if (!$fetch){
                    $sql = $conn->prepare("INSERT INTO tags(usos, nombre) VALUES (?, ?)");
                    $sql->execute([1, $tag]);
                    $last_insert_tag = $conn->lastInsertId();
                }
                else{
                    $tag_usos = $fetch["usos"];
                    $last_insert_tag = $fetch["id"];
                    $sql = $conn->prepare("UPDATE tags SET usos = ? WHERE id = ?");
                    $sql->execute([1 + (int)$tag_usos, $last_insert_tag]);
                }
                $sql = $conn->prepare("INSERT INTO posts_tags(id_post, id_tag) VALUES (?, ?)");
                $sql->execute([$last_insert, $last_insert_tag]);
            }
        }

        $conn->commit();
    }
    catch (PDOException $e){
        $conn->rollBack();
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al insertar en la base de datos."
        ]);
        exit();
    }

    if ($extension_real == "gif"){
        $fullRenombrado = strval($last_insert) . ".gif";
    }
    else{
        $fullRenombrado = strval($last_insert) . ".jpg";
    }

    $miniatura_w = 400;
    $miniatura_h = 300;

    $usar_imagick_gif = ($extension_real == "gif" && extension_loaded("imagick"));

    if ($usar_imagick_gif){
        $renombrado = strval($last_insert) . ".gif";

        $gif = new Imagick($archivo["tmp_name"]);
        $gif = $gif->coalesceImages();

        foreach ($gif as $frame){
            $ancho_frame = $frame->getImageWidth();
            $alto_frame = $frame->getImageHeight();

            $aspecto_frame = $ancho_frame / $alto_frame;
            $aspecto_miniatura = $miniatura_w / $miniatura_h;

            if ($aspecto_frame >= $aspecto_miniatura){
                $nueva_height = $miniatura_h;
                $nueva_width = $ancho_frame / ($alto_frame / $miniatura_h);
            }
            else{
                $nueva_width = $miniatura_w;
                $nueva_height = $alto_frame / ($ancho_frame / $miniatura_w);
            }

            $frame->resizeImage((int) $nueva_width, (int) $nueva_height, Imagick::FILTER_LANCZOS, 1);
            $frame->cropImage(
                $miniatura_w,
                $miniatura_h,
                (int) (($nueva_width - $miniatura_w) / 2),
                (int) (($nueva_height - $miniatura_h) / 2)
            );
            $frame->setImagePage($miniatura_w, $miniatura_h, 0, 0);
        }

        $gif = $gif->deconstructImages();
        $gif->writeImages($dir . $renombrado, true);
    }
    else{
        $renombrado = strval($last_insert) . ".jpg";

        $width = imagesx($imagen);
        $height = imagesy($imagen);

        $aspecto_original = $width / $height;
        $aspecto_miniatura = $miniatura_w / $miniatura_h;

        if ( $aspecto_original >= $aspecto_miniatura )
        {
            $nueva_height = $miniatura_h;
            $nueva_width = $width / ($height / $miniatura_h);
        }
        else
        {
            $nueva_width = $miniatura_w;
            $nueva_height = $height / ($width / $miniatura_w);
        }

        $miniatura = imagecreatetruecolor( $miniatura_w, $miniatura_h );

        imagecopyresampled($miniatura,
                        $imagen,
                        0 - ($nueva_width - $miniatura_w) / 2, 
                        0 - ($nueva_height - $miniatura_h) / 2, 
                        0, 0,
                        $nueva_width, $nueva_height,
                        $width, $height);
        imagejpeg($miniatura, $dir . $renombrado , 80);
    }

    if ($extension_real == "gif"){
        rename($archivo["tmp_name"], $fullsize . $fullRenombrado);
    }
    else{
        imagejpeg($imagen, $fullsize . $fullRenombrado);
    }

    echo json_encode([
        "ok" => true,
        "mensaje" => "OK.",
        "id" => $last_insert
    ]);
    exit();
?>