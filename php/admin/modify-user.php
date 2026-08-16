<?php
    require __DIR__ . "/../db/config.php";
    require __DIR__ . "/../../resources/parse_functions.php";
    header("Content-Type: application/json");

    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_SESSION['cuenta_rol'] == "admin" || $_SESSION['cuenta_rol'] == "mod")) {
        try {
            $accion = $_POST["accion"];
            $user_id = (int) $_POST["user_id"];

            if ($user_id === (int) $_SESSION["cuenta_id"]) {
                http_response_code(403);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "No podés actuar sobre tu propia cuenta.",
                ]);
                exit();
            }

            $sql = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
            $sql->execute([$user_id]);
            $objetivo = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$objetivo) {
                http_response_code(404);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "El usuario no existe.",
                ]);
                exit();
            }

            $rango_actor = rango_rol($_SESSION['cuenta_rol']);
            $rango_objetivo = rango_rol($objetivo["rol"]);

            if ($accion == "ban") {
                if ($rango_objetivo >= $rango_actor) {
                    http_response_code(403);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "No podés moderar a un usuario de igual o mayor jerarquía.",
                    ]);
                    exit();
                }

                if (esta_baneado($conn, $user_id)) {
                    $sql = $conn->prepare("DELETE FROM bans WHERE id_usuario = ?");
                    $sql->execute([$user_id]);

                    $sql = $conn->prepare("SELECT descripcion FROM usuarios WHERE id = ?");
                    $sql->execute([$user_id]);
                    $fila_usuario = $sql->fetch(PDO::FETCH_ASSOC);
                    $descripcion_usuario = $fila_usuario["descripcion"] ?? "";

                    if (!empty($descripcion_usuario)) {
                        $descripcion_html = formatear_descripcion($descripcion_usuario);
                    }
                    else {
                        $descripcion_html = "<p>No hay descripción.</p>";
                    }

                    http_response_code(200);
                    echo json_encode([
                        "ok" => true,
                        "accion" => "unban",
                        "rol" => $objetivo["rol"],
                        "descripcion_html" => $descripcion_html,
                        "mensaje" => "El usuario fue desbaneado.",
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

                $nuevo_rol = $objetivo["rol"];
                if ($nuevo_rol === "mod") {
                    $nuevo_rol = "user";
                    $sql = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                    $sql->execute([$nuevo_rol, $user_id]);
                }

                http_response_code(200);
                echo json_encode([
                    "ok" => true,
                    "accion" => "ban",
                    "rol" => $nuevo_rol,
                    "motivo" => $motivo,
                    "expira" => $expira,
                    "mensaje" => "El usuario fue suspendido.",
                ]);
            }
            elseif ($accion == "ascender") {
                if ($_SESSION['cuenta_rol'] !== "admin") {
                    http_response_code(403);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Solo un administrador puede ascender usuarios.",
                    ]);
                    exit();
                }

                if ($objetivo["rol"] === "user") {
                    $nuevo_rol = "mod";
                }
                elseif ($objetivo["rol"] === "mod") {
                    $nuevo_rol = "admin";
                }
                else {
                    http_response_code(400);
                    echo json_encode([
                        "ok" => false,
                        "mensaje" => "Este usuario ya es administrador.",
                    ]);
                    exit();
                }

                $sql = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                $sql->execute([$nuevo_rol, $user_id]);

                http_response_code(200);
                echo json_encode([
                    "ok" => true,
                    "accion" => "ascender",
                    "rol" => $nuevo_rol,
                    "mensaje" => "El usuario fue ascendido a $nuevo_rol.",
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