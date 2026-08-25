<?php
    require __DIR__ . "/../db/config.php";
    require __DIR__ . "/../../resources/parse_functions.php";
    header("Content-Type: application/json");

    if (!isset($_SESSION["cuenta_rol"]) || !in_array($_SESSION["cuenta_rol"], ["admin", "mod"], true)) {
        http_response_code(403);
        echo json_encode(["ok" => false, "mensaje" => "No autorizado."]);
        exit();
    }

    $offset = isset($_GET["offset"]) ? (int) $_GET["offset"] : 0;
    if ($offset < 0) {
        $offset = 0;
    }

    $condiciones = [];
    $parametros = [];

    if (isset($_GET["q"]) && !empty($_GET["q"])) {
        $busqueda = "%" . strtolower($_GET["q"]) . "%";
        $condiciones[] = "(lower(username) LIKE ? OR lower(nickname) LIKE ?)";
        $parametros[] = $busqueda;
        $parametros[] = $busqueda;
    }

    if (isset($_GET["rol"]) && in_array($_GET["rol"], ["user", "mod", "admin"], true)) {
        $condiciones[] = "rol = ?";
        $parametros[] = $_GET["rol"];
    }

    if (isset($_GET["estado"]) && $_GET["estado"] === "ban") {
        $condiciones[] = "EXISTS (SELECT 1 FROM bans WHERE bans.id_usuario = usuarios.id AND (bans.expira IS NULL OR bans.expira > NOW()))";
    }
    elseif (isset($_GET["estado"]) && $_GET["estado"] === "activo") {
        $condiciones[] = "NOT EXISTS (SELECT 1 FROM bans WHERE bans.id_usuario = usuarios.id AND (bans.expira IS NULL OR bans.expira > NOW()))";
    }

    $where = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";

    try {
        $sql = $conn->prepare("SELECT id, username, nickname, descripcion, rol, fecha_creacion FROM usuarios $where ORDER BY id DESC LIMIT " . MOD_POR_PAGINA . " OFFSET $offset");
        $sql->execute($parametros);
        $fetch_usuarios = $sql->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = [];
        foreach ($fetch_usuarios as $usuario) {
            $ban = esta_baneado($conn, (int) $usuario["id"]);
            $ruta_avatar = __DIR__ . "/../../resources/avatars/" . $usuario["id"] . ".png";
            if (file_exists($ruta_avatar)) {
                $avatar = "resources/avatars/" . $usuario["id"] . ".png?v=" . filemtime($ruta_avatar);
            }
            else {
                $avatar = "resources/avatar.png";
            }

            $usuarios[] = [
                "id" => (int) $usuario["id"],
                "username" => $usuario["username"],
                "nickname" => $usuario["nickname"],
                "descripcion" => strip_tags($usuario["descripcion"] ?? ""),
                "rol" => $usuario["rol"],
                "fecha_creacion" => $usuario["fecha_creacion"],
                "avatar" => $avatar,
                "baneado" => $ban !== null,
                "ban_motivo" => $ban["motivo"] ?? null,
                "ban_expira" => $ban["expira"] ?? null,
            ];
        }

        http_response_code(200);
        echo json_encode([
            "ok" => true,
            "usuarios" => $usuarios,
            "hay_mas" => count($fetch_usuarios) == MOD_POR_PAGINA
        ]);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => "Error al cargar los usuarios."]);
    }
?>