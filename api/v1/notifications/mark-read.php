<?php
    require __DIR__ . '/../../../php/db/config.php';
    header("Content-Type: application/json");

    if ($conn_test == 0){
        http_response_code(500);
        echo json_encode(["ok" => false, "message" => "No hay conexión."]);
        exit();
    }

    if (!isset($_SESSION["cuenta_id"])){
        http_response_code(401);
        echo json_encode(["ok" => false, "message" => "No autenticado."]);
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] != "POST"){
        http_response_code(405);
        echo json_encode(["ok" => false, "message" => "Método no permitido."]);
        exit();
    }

    try {
        $user_id = (int) $_SESSION["cuenta_id"];
        $action = $_POST["action"] ?? "";

        if ($action == "all"){
            $sql = $conn->prepare("UPDATE notificaciones SET leido = 1 WHERE id_usuario = ? AND leido = 0");
            $sql->execute([$user_id]);

            http_response_code(200);
            echo json_encode(["ok" => true]);
        }
        elseif ($action == "one"){
            $notification_id = (int) ($_POST["id"] ?? 0);

            $sql = $conn->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND id_usuario = ?");
            $sql->execute([$notification_id, $user_id]);

            http_response_code(200);
            echo json_encode(["ok" => true]);
        }
        else{
            http_response_code(400);
            echo json_encode(["ok" => false, "message" => "Acción inválida."]);
        }
    }
    catch (PDOException $e){
        http_response_code(500);
        echo json_encode(["ok" => false, "message" => "Error al consultar la API."]);
    }
?>