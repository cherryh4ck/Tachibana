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
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode(["ok" => true, "total" => $result['total']]);
    }
    catch (PDOException $e){
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Error al consultar la API."]);
    }
?>