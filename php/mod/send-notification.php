<?php
    require __DIR__ . "/../db/config.php";
    require __DIR__ . "/../../resources/parse_functions.php";
    header("Content-Type: application/json");

    if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION["cuenta_rol"]) || !in_array($_SESSION["cuenta_rol"], ["admin", "mod"], true)) {
        http_response_code(403);
        echo json_encode(["ok" => false, "mensaje" => "No autorizado."]);
        exit();
    }

    $tipos_validos = ["mod_message", "ban", "unban", "post_banned", "post_deleted"];

    $user_id = (int) ($_POST["user_id"] ?? 0);
    $mensaje = trim($_POST["mensaje"] ?? "");
    $tipo = $_POST["tipo"] ?? "mod_message";

    if ($user_id <= 0) {
        http_response_code(400);
        echo json_encode(["ok" => false, "mensaje" => "Tenés que elegir un usuario."]);
        exit();
    }

    if ($mensaje === "" || mb_strlen($mensaje) > 300) {
        http_response_code(400);
        echo json_encode(["ok" => false, "mensaje" => "El mensaje debe tener entre 1 y 300 caracteres."]);
        exit();
    }

    if (!in_array($tipo, $tipos_validos, true)) {
        $tipo = "mod_message";
    }

    try {
        $sql = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
        $sql->execute([$user_id]);
        if (!$sql->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(404);
            echo json_encode(["ok" => false, "mensaje" => "El usuario no existe."]);
            exit();
        }

        create_notification($conn, $user_id, $tipo, $mensaje);

        http_response_code(200);
        echo json_encode([
            "ok" => true,
            "mensaje" => "Notificación enviada."
        ]);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Error al enviar la notificación."]);
    }
?>