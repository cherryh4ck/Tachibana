<?php
    require __DIR__ . '/../../../php/db/config.php';
    header("Content-Type: application/json");
    
    if ($conn_test == 0){
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Sin conexión."]);
        exit();
    }

    if ($_SESSION['cuenta_rol'] == "admin" || $_SESSION['cuenta_rol'] == "mod") {
        $tachibana_version = $configuracion["general"]["version"];
        http_response_code(200);
        echo json_encode([
            "ok" => true,
            "version" => $tachibana_version
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "authorized" => false,
            "mensaje" => "No autorizado."
        ]);
    }
?>