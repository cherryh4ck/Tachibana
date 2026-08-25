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
        $condiciones[] = "lower(tags.nombre) LIKE ?";
        $parametros[] = "%" . strtolower($_GET["q"]) . "%";
    }

    $where = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";

    try {
        $sql = $conn->prepare("SELECT tags.id, tags.nombre, tags.usos, COUNT(posts_tags.id) AS en_uso FROM tags LEFT JOIN posts_tags ON posts_tags.id_tag = tags.id $where GROUP BY tags.id, tags.nombre, tags.usos ORDER BY tags.usos DESC, tags.nombre ASC LIMIT " . MOD_POR_PAGINA . " OFFSET $offset");
        $sql->execute($parametros);
        $fetch_tags = $sql->fetchAll(PDO::FETCH_ASSOC);

        $tags = [];
        foreach ($fetch_tags as $tag) {
            $tags[] = [
                "id" => (int) $tag["id"],
                "nombre" => $tag["nombre"],
                "usos" => (int) $tag["usos"],
                "en_uso" => (int) $tag["en_uso"],
            ];
        }

        http_response_code(200);
        echo json_encode([
            "ok" => true,
            "tags" => $tags,
            "hay_mas" => count($fetch_tags) == MOD_POR_PAGINA
        ]);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Error al cargar los tags."]);
    }
?>