<?php
    require __DIR__ . "/../db/config.php";
    require __DIR__ . "/../../resources/parse_functions.php";
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_SESSION['cuenta_rol'] == "admin" || $_SESSION['cuenta_rol'] == "mod")) {
        try {
            $accion = $_POST["accion"];
            $user_id = $_POST["user_id"];

            if ($accion == "ban") {
                if (esta_baneado($conn, $user_id)) {
                    $sql = $conn->prepare("DELETE FROM bans WHERE id_usuario = ?");
                    $sql->execute([$user_id]);
                    http_response_code(200);
                    echo json_encode([
                        "ok" => true,
                        "mensaje" => "El usuario de ID $user_id fue desbaneado.",
                    ]);
                    exit();
                }

                $motivo = trim($_POST["motivo"]);
                if ($motivo === "") {
                    $motivo = "Sin especificar.";
                }

                $expira = trim($_POST["fecha_expiracion"]);
                if ($expira === "") {
                    $expira = null;
                }

                $sql = $conn->prepare("INSERT INTO bans(id_usuario, motivo, expira) VALUES (?, ?, ?)");
                $sql->execute([$user_id, $motivo, $expira]);
                http_response_code(200);
                echo json_encode([
                    "ok" => true,
                    "mensaje" => "El usuario de ID $user_id fue baneado.",
                ]);
            }
            else {
                http_response_code(400);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "Acción no válida."
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "ok" => false,
                "mensaje" => "Error: $e"
            ]);
        }
    }
    else {
        http_response_code(403);
        echo json_encode([
            "authorized" => false,
            "mensaje" => "No autorizado."
        ]);
    }
?>