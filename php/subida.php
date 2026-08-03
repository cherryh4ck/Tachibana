<?php
    if ($_SERVER["REQUEST_METHOD"] != "POST"){
        header("Location: ../index.php");
        exit();
    }

    require "db/config.php";

    if ($conn_test == 0){
        header("Location: ../error.php?id=9");
        exit();
    }

    $cuenta_obligatoria = $configuracion["general"]["cuenta_obligatoria"];

    if ($cuenta_obligatoria == 1 && !isset($_SESSION["cuenta_id"])){
        header("Location: ../login.php");
        exit();
    }

    $maxSize = 6228792; // esta variable es para el tamaño maximo del archivo, se cambia si el archivo es de tipo gif

    // datos de entrada
    $post_titulo = htmlspecialchars($_POST["titulo"]);
    $post_categoria = $_POST["categoria"];
    $post_descripcion = nl2br(htmlspecialchars($_POST["descripcion"]));
    $post_autor_id = $_SESSION["cuenta_id"];
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
        header("Location: ../index.php");
        exit();
    }

    if (!is_numeric($post_categoria)){
        header("Location: ../error.php?id=7");
        exit();
    }

    try{
        $sql = $conn->prepare("SELECT id FROM categorias WHERE id = ?");
        $sql->execute([$post_categoria]);
        if (!$sql->fetch(PDO::FETCH_ASSOC)){
            header("Location: ../error.php?id=7");
            exit();
        }
    }
    catch (PDOException $e){
        header("Location: ../error.php?id=9");
        exit();
    }

    $dir = "../galeria/";
    $fullsize = "../galeria/fullsize/";

    $tamaño = filesize($archivo["tmp_name"]);
    $imagesize = getimagesize($archivo["tmp_name"]);

    if (!$imagesize){
        header("Location: ../error.php?id=3");
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
        header("Location: ../error.php?id=3");
        exit();
    }

    try{
        $conn->beginTransaction();

        $sql = $conn->prepare("INSERT INTO posts(id_autor, id_categoria, titulo, descripcion, anonimo) VALUES (?, ?, ?, ?, ?);");
        $sql->execute([$post_autor_id, $post_categoria, $post_titulo, $post_descripcion, $post_anonimo]);

        $last_insert = $conn->lastInsertId();
        $renombrado = strval($last_insert) . ".jpg";
        if ($extension_real == "gif"){
            $fullRenombrado = strval($last_insert) . ".gif";
        }
        else{
            $fullRenombrado = $renombrado;
        }

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
        header("Location: ../error.php?id=9");
        exit();
    }

    $miniatura_w = 400;
    $miniatura_h = 300;

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
    if ($extension_real == "gif"){
        rename($archivo["tmp_name"], $fullsize . $fullRenombrado);
    }
    else{
        imagejpeg($imagen, $fullsize . $fullRenombrado);
    }

    header("Location: ../post.php?id=" . strval($last_insert));
    exit();
?>