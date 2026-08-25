<?php
    require __DIR__ . "/../db/config.php";
    require __DIR__ . "/../../resources/parse_functions.php";
    header("Content-Type: application/json");

    if (!isset($_SESSION["cuenta_rol"]) || !in_array($_SESSION["cuenta_rol"], ["admin", "mod"], true)) {
        http_response_code(403);
        echo json_encode(["ok" => false, "mensaje" => "No autorizado."]);
        exit();
    }

    $offset = isset($_GET["offset"]) ? (int) $_GET["offset"] : 0;
    if ($offset < 0) {
        $offset = 0;
    }

    $condiciones = [];
    $parametros = [];

    if (isset($_GET["q"]) && !empty($_GET["q"])) {
        $condiciones[] = "lower(posts.titulo) LIKE ?";
        $parametros[] = "%" . strtolower($_GET["q"]) . "%";
    }

    if (isset($_GET["categoria"]) && !empty($_GET["categoria"]) && $_GET["categoria"] !== "all") {
        $condiciones[] = "categorias.nombre = ?";
        $parametros[] = $_GET["categoria"];
    }

    if (isset($_GET["estado"])) {
        if ($_GET["estado"] === "sticky") {
            $condiciones[] = "posts.sticky = 1";
        }
        elseif ($_GET["estado"] === "archivado") {
            $condiciones[] = "posts.archivado = 1";
        }
        elseif ($_GET["estado"] === "baneado") {
            $condiciones[] = "posts.baneado = 1";
        }
    }

    $where = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";

    try {
        $sql = $conn->prepare("SELECT posts.id, posts.titulo, posts.sticky, posts.archivado, posts.baneado, posts.fecha_creacion, posts.id_autor, categorias.nombre AS categoria, usuarios.username AS autor_username, usuarios.nickname AS autor_nickname FROM posts INNER JOIN categorias ON categorias.id = posts.id_categoria INNER JOIN usuarios ON usuarios.id = posts.id_autor $where ORDER BY posts.sticky DESC, posts.id DESC LIMIT " . MOD_POR_PAGINA . " OFFSET $offset");
        $sql->execute($parametros);
        $fetch_posts = $sql->fetchAll(PDO::FETCH_ASSOC);

        $posts = [];
        foreach ($fetch_posts as $post) {
            $miniatura = null;
            if ($post["baneado"] == 0) {
                if (file_exists(__DIR__ . "/../../galeria/" . $post["id"] . ".jpg")) {
                    $miniatura = "galeria/" . $post["id"] . ".jpg";
                }
                elseif (file_exists(__DIR__ . "/../../galeria/" . $post["id"] . ".gif")) {
                    $miniatura = "galeria/" . $post["id"] . ".gif";
                }
            }

            $posts[] = [
                "id" => (int) $post["id"],
                "titulo" => $post["titulo"],
                "categoria" => $post["categoria"],
                "sticky" => (int) $post["sticky"],
                "archivado" => (int) $post["archivado"],
                "baneado" => (int) $post["baneado"],
                "fecha_creacion" => $post["fecha_creacion"],
                "autor_id" => (int) $post["id_autor"],
                "autor_username" => $post["autor_username"],
                "autor_nickname" => $post["autor_nickname"],
                "miniatura" => $miniatura,
            ];
        }

        http_response_code(200);
        echo json_encode([
            "ok" => true,
            "posts" => $posts,
            "hay_mas" => count($fetch_posts) == MOD_POR_PAGINA
        ]);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Error al cargar los posts."]);
    }
?>