<?php
    require __DIR__ . "/../db/config.php";
    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_SESSION['cuenta_rol'] == "admin" || $_SESSION['cuenta_rol'] == "mod")) {
        try {
            $accion = $_POST["accion"];
            if ($accion == "sticky") {
                $post_id = $_POST["post_id"];
                $conn = new PDO("mysql:host=$host:$puerto;dbname=$db", $user, $pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = $conn->prepare("UPDATE posts SET sticky = (@new := CASE WHEN sticky = 1 THEN 0 ELSE 1 END) WHERE id = ?");
                $sql->execute([$post_id]);

                $newValue = $conn->query("SELECT @new")->fetchColumn();

                http_response_code(200);
                header("Content-Type: application/json");
                echo json_encode([
                    "authorized" => true,
                    "ok" => true,
                    "mensaje" => "STICKY cambiado al post $post_id.",
                    "value" => $newValue
                ]);
            }
        }
        catch (PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode([
                "authorized" => true,
                "ok" => false,
                "mensaje" => "Error: $e"
            ]);
        }
    }
    else {
        http_response_code(403);
        header("Content-Type: application/json");
        echo json_encode([
            "authorized" => false,
            "mensaje" => "No autorizado."
        ]);
    }
?>