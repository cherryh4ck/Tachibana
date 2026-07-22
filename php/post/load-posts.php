<?php
    require "../db/config.php";
    require "../../resources/parse_functions.php";

    header("Content-Type: application/json");

    if ($conn_test == 0){
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Sin conexión."]);
        exit();
    }

    $offset = isset($_GET["offset"]) ? (int) $_GET["offset"] : 0;
    if ($offset < 0){
        $offset = 0;
    }

    try {
        $filtro = construir_filtro_posts($conn);
        $where = $filtro["where"];
        $parametros = $filtro["parametros"];
        $orden = $filtro["orden"];

        $sql = $conn->prepare("SELECT * from posts $where ORDER BY sticky DESC, id $orden LIMIT " . POSTS_POR_PAGINA . " OFFSET $offset");
        $sql->execute($parametros);
        $fetch_posts = $sql->fetchAll(PDO::FETCH_ASSOC);

        $html = "";
        foreach ($fetch_posts as $post){
            $html .= renderizar_post_card($post, $conn);
        }

        echo json_encode([
            "ok" => true,
            "html" => $html,
            "hay_mas" => count($fetch_posts) == POSTS_POR_PAGINA
        ]);
    }
    catch (PDOException $e){
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Error al cargar los posts."]);
    }
?>
