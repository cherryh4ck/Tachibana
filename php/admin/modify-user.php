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
            $user_id = (int) $_POST["user_id"];

            if ($user_id === (int) $_SESSION["cuenta_id"]) {
                http_response_code(403);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "No podés actuar sobre tu propia cuenta.",
                ]);
                exit();
            }

            $sql = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
            $sql->execute([$user_id]);
            $objetivo = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$objetivo) {
                http_response_code(404);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "El usuario no existe.",
                ]);
                exit();
            }

            $rango_actor = rango_rol($_SESSION['cuenta_rol']);
            $rango_objetivo = rango_rol($objetivo["rol"]);

            if ($accion == "ban") {
                if ($rango_objetivo >= $rango_actor) {
                    http_response_code(403);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "No podés moderar a un usuario de igual o mayor jerarquía.",
                    ]);
                    exit();
                }

                if (esta_baneado($conn, $user_id)) {
                    $sql = $conn->prepare("DELETE FROM bans WHERE id_usuario = ?");
                    $sql->execute([$user_id]);

                    $sql = $conn->prepare("SELECT descripcion FROM usuarios WHERE id = ?");
                    $sql->execute([$user_id]);
                    $fila_usuario = $sql->fetch(PDO::FETCH_ASSOC);
                    $descripcion_usuario = $fila_usuario["descripcion"] ?? "";

                    if (!empty($descripcion_usuario)) {
                        $descripcion_html = formatear_descripcion($descripcion_usuario);
                    }
                    else {
                        $descripcion_html = "<p>No hay descripción.</p>";
                    }

                    http_response_code(200);
                    echo json_encode([
                        "ok" => true,
                        "accion" => "unban",
                        "rol" => $objetivo["rol"],
                        "descripcion_html" => $descripcion_html,
                        "mensaje" => "El usuario fue desbaneado.",
                    ]);
                    exit();
                }

                $motivo = trim($_POST["motivo"]);
                if ($motivo === "") {
                    $motivo = "Sin especificar.";
                }

                $expira = trim($_POST["fecha_expiracion"]);
                if ($expira === "") {
                    $expira = null;
                }

                $sql = $conn->prepare("INSERT INTO bans(id_usuario, motivo, expira) VALUES (?, ?, ?)");
                $sql->execute([$user_id, $motivo, $expira]);

                $nuevo_rol = $objetivo["rol"];
                if ($nuevo_rol === "mod") {
                    $nuevo_rol = "user";
                    $sql = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                    $sql->execute([$nuevo_rol, $user_id]);
                }

                http_response_code(200);
                echo json_encode([
                    "ok" => true,
                    "accion" => "ban",
                    "rol" => $nuevo_rol,
                    "motivo" => $motivo,
                    "expira" => $expira,
                    "mensaje" => "El usuario fue suspendido.",
                ]);
            }
            elseif ($accion == "ascender") {
                if ($_SESSION['cuenta_rol'] !== "admin") {
                    http_response_code(403);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Solo un administrador puede ascender usuarios.",
                    ]);
                    exit();
                }

                if ($objetivo["rol"] === "user") {
                    $nuevo_rol = "mod";
                }
                elseif ($objetivo["rol"] === "mod") {
                    $nuevo_rol = "admin";
                }
                else {
                    http_response_code(400);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Este usuario ya es administrador.",
                    ]);
                    exit();
                }

                $sql = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                $sql->execute([$nuevo_rol, $user_id]);

                http_response_code(200);
                echo json_encode([
                    "ok" => true,
                    "accion" => "ascender",
                    "rol" => $nuevo_rol,
                    "mensaje" => "El usuario fue ascendido a $nuevo_rol.",
                ]);
            }
            elseif ($accion == "delete") {
                // antes de empezar esta acción, tenemos que eliminar todos los posts
                // y comentarios hecho por la persona
                // empezamos con los comentarios con imagenes hechos por el usuario 
                $sql = $conn->prepare("SELECT * FROM posts_comentarios WHERE id_autor = ? AND imagen_adjuntada = 1");
                $sql->execute([$user_id]);
                $comentarios = $sql->fetchAll(PDO::FETCH_ASSOC);
                if ($comentarios) {
                    foreach ($comentarios as $comentario) {
                        eliminarComentario($comentario["id"], $comentario["id_post"]);
                    }
                }
                // eliminamos todos los comentarios
                $sql = $conn->prepare("DELETE FROM posts_comentarios WHERE id_autor = ?");
                $sql->execute([$user_id]);
                // eliminamos todos los posts del usuario
                $sql = $conn->prepare("SELECT * FROM posts WHERE id_autor = ?");
                $sql->execute([$user_id]);
                $posts = $sql->fetchAll(PDO::FETCH_ASSOC);
                if ($posts) {
                    foreach ($posts as $post) {
                        eliminarPost($post["id"]);
                        $dir = __DIR__ . "/../../resources/posts/" . $post["id"]; 
                        eliminarDirectorio($dir);
                        $sql = $conn->prepare("DELETE FROM posts_tags WHERE id_post = ?");
                        $sql->execute([$post["id"]]);
                        $sql = $conn->prepare("DELETE FROM posts_comentarios WHERE id_post = ?");
                        $sql->execute([$post["id"]]);
                        $sql = $conn->prepare("DELETE FROM posts WHERE id = ?");
                        $sql->execute([$post["id"]]);
                    }
                }
                $sql = $conn->prepare("DELETE FROM bans WHERE id_usuario = ?");
                $sql->execute([$user_id]);
                $sql = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                $sql->execute([$user_id]);
                http_response_code(200);
                echo json_encode([
                    "authorized" => true,
                    "ok" => true,
                    "mensaje" => "Usuario $user_id eliminado."
                ]);
            }
            elseif ($accion == "edit") {
                if ($rango_objetivo >= $rango_actor) {
                    http_response_code(403);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "No podés modificar a un usuario de igual o mayor jerarquía.",
                    ]);
                    exit();
                }

                $nickname = preg_replace("/\s\s+/", "", trim($_POST["nickname"] ?? ""));
                if (strlen($nickname) < 3 || strlen($nickname) > 19) {
                    http_response_code(400);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "El nickname debe tener entre 3 y 19 caracteres.",
                    ]);
                    exit();
                }

                $username = trim($_POST["username"] ?? "");
                if (!preg_match('/^(?!.*_{2,})[a-zA-Z0-9_]{3,16}$/', $username)) {
                    http_response_code(400);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "El username debe tener entre 3 y 16 caracteres (letras, números y guiones bajos).",
                    ]);
                    exit();
                }

                $descripcion = $_POST["descripcion"] ?? "";
                if (strlen($descripcion) > 500) {
                    http_response_code(400);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "La descripción es demasiado larga.",
                    ]);
                    exit();
                }
                $descripcion_guardada = nl2br(htmlspecialchars($descripcion));

                $sql = $conn->prepare("UPDATE usuarios SET username = ?, nickname = ?, descripcion = ? WHERE id = ?");
                $sql->execute([$username, htmlspecialchars($nickname), $descripcion_guardada, $user_id]);

                if (!empty($descripcion_guardada)) {
                    $descripcion_html = formatear_descripcion($descripcion_guardada);
                }
                else {
                    $descripcion_html = "<p>No hay descripción.</p>";
                }

                http_response_code(200);
                echo json_encode([
                    "ok" => true,
                    "accion" => "edit",
                    "nickname" => htmlspecialchars($nickname),
                    "username" => $username,
                    "descripcion_html" => $descripcion_html,
                    "mensaje" => "Los datos del usuario fueron actualizados.",
                ]);
            }
            elseif ($accion == "delete_avatar") {
                if ($rango_objetivo >= $rango_actor) {
                    http_response_code(403);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "No podés modificar a un usuario de igual o mayor jerarquía.",
                    ]);
                    exit();
                }

                $ruta_avatar = __DIR__ . "/../../resources/avatars/$user_id.png";
                if (!file_exists($ruta_avatar)) {
                    http_response_code(400);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Este usuario no tiene un avatar personalizado.",
                    ]);
                    exit();
                }

                unlink($ruta_avatar);

                http_response_code(200);
                echo json_encode([
                    "ok" => true,
                    "accion" => "delete_avatar",
                    "mensaje" => "El avatar fue eliminado.",
                ]);
            }
            else {
                http_response_code(400);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "Acción no válida."
                ]);
            }
        }
        catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "Ese nombre de usuario ya está en uso.",
                ]);
            }
            else {
                http_response_code(500);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "Error: $e"
                ]);
            }
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