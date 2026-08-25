<?php
    require __DIR__ . "/../db/config.php";
    require __DIR__ . "/../../resources/parse_functions.php";
    header("Content-Type: application/json");

    if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION["cuenta_rol"]) || !in_array($_SESSION["cuenta_rol"], ["admin", "mod"], true)) {
        http_response_code(403);
        echo json_encode(["ok" => false, "mensaje" => "No autorizado."]);
        exit();
    }

    $accion = $_POST["accion"] ?? "";
    $tag_id = (int) ($_POST["tag_id"] ?? 0);

    if ($accion !== "delete" || $tag_id <= 0) {
        http_response_code(400);
        echo json_encode(["ok" => false, "mensaje" => "Acción no válida."]);
        exit();
    }

    try {
        $sql = $conn->prepare("SELECT nombre FROM tags WHERE id = ?");
        $sql->execute([$tag_id]);
        $tag = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$tag) {
            http_response_code(404);
            echo json_encode(["ok" => false, "mensaje" => "El tag no existe."]);
            exit();
        }

        $sql = $conn->prepare("DELETE FROM posts_tags WHERE id_tag = ?");
        $sql->execute([$tag_id]);

        $sql = $conn->prepare("DELETE FROM tags WHERE id = ?");
        $sql->execute([$tag_id]);

        http_response_code(200);
        echo json_encode([
            "ok" => true,
            "mensaje" => "El tag \"" . $tag["nombre"] . "\" fue eliminado."
        ]);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Error al eliminar el tag."]);
    }
?>