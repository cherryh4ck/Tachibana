<?php
    require __DIR__ . '/../../../php/db/config.php';
    header("Content-Type: application/json");
    
    if ($conn_test == 0){
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Sin conexión."]);
        exit();
    }

    try {
        $query = "SELECT COUNT(*) AS total FROM posts";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $resultPosts = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "SELECT COUNT(*) AS total FROM posts_comentarios";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $resultComentarios = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "SELECT COUNT(*) AS total FROM tags";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $resultTags = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "SELECT COUNT(*) AS total FROM usuarios";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $resultUsuarios = $stmt->fetch(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            "ok" => true, 
            "total_posts" => $resultPosts['total'],
            "total_comentarios" => $resultComentarios['total'],
            "total_tags" => $resultTags['total'],
            "total_usuarios" => $resultUsuarios['total']
        ]);
    }
    catch (PDOException $e){
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Error al consultar la API."]);
    }
?>