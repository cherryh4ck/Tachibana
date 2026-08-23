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

    try {
        $user_id = (int) $_SESSION["cuenta_id"];

        $conn->exec("DELETE FROM notificaciones WHERE leido = 1 AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $sql = $conn->prepare("SELECT id, tipo, texto, leido, fecha_creacion FROM notificaciones WHERE id_usuario = ? ORDER BY id DESC LIMIT 20");
        $sql->execute([$user_id]);
        $notifications = $sql->fetchAll(PDO::FETCH_ASSOC);

        $sql = $conn->prepare("SELECT COUNT(*) AS total FROM notificaciones WHERE id_usuario = ? AND leido = 0");
        $sql->execute([$user_id]);
        $unread = (int) $sql->fetch(PDO::FETCH_ASSOC)["total"];

        http_response_code(200);
        echo json_encode([
            "ok" => true,
            "unread" => $unread,
            "notifications" => $notifications
        ]);
    }
    catch (PDOException $e){
        http_response_code(500);
        echo json_encode(["ok" => false, "message" => "Error al consultar la API."]);
    }
?>