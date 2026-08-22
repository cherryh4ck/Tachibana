<?php
    require __DIR__ . "/../db/config.php";
    require __DIR__ . "/../../resources/parse_functions.php";
    header("Content-Type: application/json");

    function eliminarPost(int $post_id) {
        if (file_exists(__DIR__ . "/../../galeria/$post_id.jpg")) {
            unlink(__DIR__ . "/../../galeria/$post_id.jpg");
            unlink(__DIR__ . "/../../galeria/fullsize/$post_id.jpg");
        }
        else{
            if (extension_loaded("imagick")){
                unlink(__DIR__ . "/../../galeria/$post_id.gif");
                unlink(__DIR__ . "/../../galeria/fullsize/$post_id.gif");
            }
            else{
                unlink(__DIR__ . "/../../galeria/$post_id.jpg");
                unlink(__DIR__ . "/../../galeria/fullsize/$post_id.gif");
            }
        }
    }

    function eliminarComentario(int $comentario_id, int $post_id) {
        if (file_exists(__DIR__ . "/../../resources/posts/$post_id/$comentario_id.png")) {
            unlink(__DIR__ . "/../../resources/posts/$post_id/$comentario_id.png");
        }
    }

    function eliminarDirectorio(string $dirPath): void {
        if (!is_dir($dirPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $object) {
            if ($object->isDir()) {
                rmdir($object->getRealPath());
            } else {
                unlink($object->getRealPath());
            }
        }

        rmdir($dirPath);
    }


    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_SESSION['cuenta_rol'] == "admin" || $_SESSION['cuenta_rol'] == "mod")) {
        try {
            $accion = $_POST["accion"];
            $post_id = $_POST["post_id"];

            if ($accion == "sticky") {
                $sql = $conn->prepare("UPDATE posts SET sticky = (@new := CASE WHEN sticky = 1 THEN 0 ELSE 1 END) WHERE id = ?");
                $sql->execute([$post_id]);

                $newValue = $conn->query("SELECT @new")->fetchColumn();

                http_response_code(200);
                echo json_encode([
                    "authorized" => true,
                    "ok" => true,
                    "mensaje" => "STICKY cambiado al post $post_id.",
                    "value" => $newValue
                ]);
            }
            else if ($accion == "ban") {
                $motivo = $_POST["motivo"];
                $eliminar_recursos = $_POST["eliminar_recursos"] ?? "false";
                $banear_usuario = $_POST["banear_usuario"] ?? "false";
                $sql = $conn->prepare("UPDATE posts SET baneado = 1, baneado_motivo = ? WHERE id = ?");
                $sql->execute([$motivo, $post_id]);

                $sql = $conn->prepare("UPDATE posts SET sticky = 0 WHERE id = ?");
                $sql->execute([$post_id]);

                if ($eliminar_recursos === "true") {
                    eliminarPost($post_id);
                }

                if ($banear_usuario === "true") {
                    $sql = $conn->prepare("SELECT * FROM posts WHERE id = ?");
                    $sql->execute([$post_id]);
                    $fetch = $sql->fetch(PDO::FETCH_ASSOC);
                    $user_id = (int) $fetch["id_autor"];
                    if ($user_id === (int) $_SESSION["cuenta_id"]) {
                        http_response_code(200);
                        echo json_encode([
                            "authorized" => true,
                            "ok" => true,
                            "mensaje" => "Post $post_id baneado con motivo: $motivo. Sin embargo, no se pudo banear la cuenta.",
                            "eliminar_recursos" => $eliminar_recursos
                        ]);
                        exit();
                    }

                    $sql = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
                    $sql->execute([$user_id]);
                    $objetivo = $sql->fetch(PDO::FETCH_ASSOC);

                    $rango_actor = rango_rol($_SESSION['cuenta_rol']);
                    $rango_objetivo = rango_rol($objetivo["rol"]);

                    if ($rango_objetivo >= $rango_actor) {
                        http_response_code(200);
                        echo json_encode([
                            "authorized" => true,
                            "ok" => true,
                            "mensaje" => "Post $post_id baneado con motivo: $motivo. Sin embargo, no se pudo banear la cuenta.",
                            "eliminar_recursos" => $eliminar_recursos
                        ]);
                        exit();
                    }

                    $motivo = "Un post tuyo fue moderado";
                    $expira = date('Y-m-d H:i:s', strtotime('+12 hours'));

                    $sql = $conn->prepare("INSERT INTO bans(id_usuario, motivo, expira) VALUES (?, ?, ?)");
                    $sql->execute([$user_id, $motivo, $expira]);
                }

                http_response_code(200);
                echo json_encode([
                    "authorized" => true,
                    "ok" => true,
                    "mensaje" => "Post $post_id baneado con motivo: $motivo.",
                    "eliminar_recursos" => $eliminar_recursos
                ]);
            }
            else if ($accion == "delete") {
                // eliminamos recursos del post antes por si acaso
                eliminarPost($post_id);
                // eliminamos los recursos de los comentarios
                $dir = __DIR__ . "/../../resources/posts/$post_id"; 
                eliminarDirectorio($dir);
                // ahora si la query
                $sql = $conn->prepare("DELETE FROM posts_tags WHERE id_post = ?");
                $sql->execute([$post_id]);
                $sql = $conn->prepare("DELETE FROM posts_comentarios WHERE id_post = ?");
                $sql->execute([$post_id]);
                $sql = $conn->prepare("DELETE FROM posts WHERE id = ?");
                $sql->execute([$post_id]);
                http_response_code(200);
                echo json_encode([
                    "authorized" => true,
                    "ok" => true,
                    "mensaje" => "Post $post_id eliminado."
                ]);
            }
            else if ($accion == "archive") {
                $sql = $conn->prepare("UPDATE posts SET archivado = (@new := CASE WHEN archivado = 1 THEN 0 ELSE 1 END) WHERE id = ?");
                $sql->execute([$post_id]);

                $newValue = $conn->query("SELECT @new")->fetchColumn();

                http_response_code(200);
                echo json_encode([
                    "authorized" => true,
                    "ok" => true,
                    "mensaje" => "Post $post_id archivado.",
                    "value" => $newValue
                ]);
            }
            else {
                http_response_code(400);
                echo json_encode([
                    "authorized" => true,
                    "ok" => false,
                    "mensaje" => "Acción no válida."
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "authorized" => true,
                "ok" => false,
                "mensaje" => "Error: $e" // no hace falta ocultar el error, supongo
            ]);
        }
    }
    else {
        http_response_code(403);
        echo json_encode([
            "authorized" => false,
            "mensaje" => "No autorizado."
        ]);
    }
?>