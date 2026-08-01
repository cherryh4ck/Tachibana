<?php
    require "../db/config.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        if (isset($_SESSION["cuenta_id"]) && isset($_POST["id_comentario"]) && isset($_POST["comentario"])){
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
                $conn = new PDO("mysql:host=$host:$puerto;dbname=$db", $user, $pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = $conn->prepare("SELECT * FROM posts WHERE id = ?");
                $sql->execute([$comentario_id]);
                $fetch = $sql->fetch(PDO::FETCH_ASSOC);
                if ($fetch){
                    $post_autor_id = $fetch["id_autor"];
                    $post_anonimo = $fetch["anonimo"];
                    $baneado = $fetch["baneado"];
                    $original_poster = 0;

                    if ($baneado == 1){
                        header("Location: ../../post.php?id=$comentario_id");
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

                    header("Location: ../../post.php?id=$comentario_id");
                    exit();
                }
            }
            catch (PDOException $e){
                header("Location: ../../error.php?id=9");
                exit();
            }
        }
    }
    header("Location: ../../index.php");
?>
