<?php
    // TODO TOTAL:
    // - más chequeos de sanidad (tamaño mínimo, máxima resolución, sesión etc etc etc)
    // - organizar el código, parece un desastre xd
    // - terminar tags
    require "db/config.php";
    error_reporting(E_ERROR | E_PARSE);
    session_start();

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
    $post_tags = $_POST["tags"];
    $archivo = $_FILES["archivo"];

    if (!isset($archivo)){ // chequear si de entrada tenemos un archivo
        header("Location: ../index.php");
        exit();
    }

    $renombrado = "";
    $archivos = scandir("../galeria/");
    
    try {   
        $sql = $conn->prepare("SELECT MAX(id) as max FROM posts");
        $sql->execute();

        $fetch = $sql->fetch(PDO::FETCH_ASSOC);
        $total_archivos = $fetch["max"] + 1;
    }
    catch (PDOException $e){
        header("Location: ../error.php?id=9");
        exit();
    }

    $info = pathinfo($archivo["name"]);

    $renombrado = strval($total_archivos) . ".jpg";
    if ($info["extension"] == "gif"){
        $fullRenombrado = strval($total_archivos) . ".gif";
    }
    else{
        $fullRenombrado = $renombrado;
    }
    $dir = "../galeria/";
    $fullsize = "../galeria/fullsize/";


    $nombregenerado = strval(rand(0, 100000000000)) . ".jpg";
    // chequeos de sanidad
    list($x, $y) = getimagesize($archivo["tmp_name"]);
    $tamaño = filesize($archivo["tmp_name"]);

    if ($x >= 400 and $y >= 300){
        if ($info["extension"] == "png"){
            $imagen = imagecreatefrompng($archivo["tmp_name"]);
        }
        else if ($info["extension"] == "jpg" or $info["extension"] == "jpeg" or $info["extension"] == "jfif"){
            $imagen = imagecreatefromjpeg($archivo["tmp_name"]);
        }
        else if ($info["extension"] == "webp"){
            $imagen = imagecreatefromwebp($archivo["tmp_name"]);
        }
        else if ($info["extension"] == "gif"){
            $imagen = imagecreatefromgif($archivo["tmp_name"]);
            $maxSize = 26228792;
        }

        if (!$imagen){
            header("Location: ../error.php?id=3");
            exit();
        }
        else{
            if (!($tamaño < $maxSize)){
                header("Location: ../error.php?id=8");
                exit();
            }

            try{
                $sql = $conn->prepare("INSERT INTO posts(id_autor, id_categoria, titulo, descripcion, anonimo) VALUES (?, ?, ?, ?, ?);");
                $sql->execute([$post_autor_id, $post_categoria, $post_titulo, $post_descripcion, $post_anonimo]);

                $last_insert = $conn->lastInsertId();
                if (!(empty($post_tags))){
                    $tags = explode(",", $post_tags);
                    foreach ($tags as $tag){
                        $tag = strtolower(trim($tag));
                        if (empty($tag) || strlen($tag) > 20 || strlen($tag) < 4){
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
            }
            catch (PDOException $e){
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
            if ($info["extension"] == "gif"){
                rename($archivo["tmp_name"], $fullsize . $fullRenombrado);
            }
            else{
                $fullsizeimg = imagejpeg($imagen, $fullsize . $fullRenombrado);
            }
            header("Location: ../post.php?id=" . strval($total_archivos));
            exit();
        }
    }
    else{
        header("Location: ../error.php?id=7");
        exit();
    }
    // chdir("../galeria/");
?>
