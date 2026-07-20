<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !is_dir(__DIR__ . "/../galeria")) {
        $host = $_POST["host"] ?? "";
        $puerto = $_POST["puerto"] ?? "";
        $usuario = $_POST["usuario"] ?? "";
        $password = $_POST["password"] ?? "";
        $database = $_POST["database"] ?? "";
        $cuenta_obligatoria = isset($_POST["cuenta_obligatoria"]) ? 1 : 0;

        session_start();
        session_unset();
        session_destroy();

        unset($_COOKIE["auth"]);
        unset($_COOKIE["ult_act"]);
        setcookie("auth", "", 1, "/");
        setcookie("ult_act", "", 1, "/"); 

        require __DIR__ . "/../resources/update-config.php";
        actualizar_config([
            "network" => [
                "host" => $host,
                "puerto" => $puerto,
                "username" => $usuario,
                "password" => $password,
                "database" => $database
            ],
            "general" => [
                "cuenta_obligatoria" => $cuenta_obligatoria
            ]
        ]);

        require __DIR__ . "/db/config.php";
        $paso = "conexion";
        try {
            $conn = new PDO("mysql:host=$host:$puerto", $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $paso = "crear";
            $password = password_hash("admin", PASSWORD_BCRYPT);
            $sql = "CREATE DATABASE $db; USE $db; CREATE TABLE settings(id INT AUTO_INCREMENT PRIMARY KEY, motd_titu TEXT, motd_desc TEXT, motd_key TEXT); CREATE TABLE usuarios(id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, nickname VARCHAR(100) NOT NULL, descripcion TEXT, auth_cookie TEXT, rol VARCHAR(100) NOT NULL DEFAULT 'user', fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP, ult_act DATETIME, ult_act_activo TINYINT(1) DEFAULT 0); CREATE TABLE categorias(id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL UNIQUE); CREATE TABLE posts(id INT AUTO_INCREMENT PRIMARY KEY, id_autor INT NOT NULL, id_categoria INT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion TEXT NOT NULL, anonimo TINYINT(1) NOT NULL, sticky TINYINT(1) NOT NULL DEFAULT 0, archivado TINYINT(1) NOT NULL DEFAULT 0, fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (id_autor) REFERENCES usuarios(id) ON DELETE CASCADE, FOREIGN KEY (id_categoria) REFERENCES categorias(id)); CREATE TABLE tags(id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, usos INT NOT NULL); CREATE TABLE posts_tags(id INT AUTO_INCREMENT PRIMARY KEY , id_post INT NOT NULL, id_tag INT NOT NULL, FOREIGN KEY (id_post) REFERENCES posts(id), FOREIGN KEY (id_tag) REFERENCES tags(id)); CREATE TABLE posts_comentarios(id INT AUTO_INCREMENT PRIMARY KEY, id_post INT NOT NULL, id_autor INT NOT NULL, fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP, comentario TEXT NOT NULL, imagen_adjuntada TINYINT(1) NOT NULL, original_poster TINYINT(1) NOT NULL, FOREIGN KEY (id_post) REFERENCES posts(id)); INSERT INTO settings(motd_titu, motd_desc) VALUES ('Tachibana', 'Bienvenido a Tachibana!'); INSERT INTO usuarios(username, password, nickname, rol) VALUES ('root', '$password', 'admin', 'admin'); INSERT INTO categorias(nombre) VALUES ('any'), ('anime'), ('manga'), ('games'), ('pol'), ('tech'), ('music'), ('movie'), ('coding');";
            $conn->exec($sql);

            $carpetas = ["galeria", "galeria/fullsize", "resources/avatars", "resources/posts"];
            foreach ($carpetas as $carpeta) {
                mkdir(__DIR__ . "/../" . $carpeta);
            }

            header("Content-Type: application/json");
            echo json_encode([
                "ok" => true,
                "mensaje" => "Instalado"
            ]);
        }
        catch(PDOException $e){
            if ($paso == "conexion") {
                header("Content-Type: application/json");
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "No se pudo conectar a la base de datos."
                ]);
            }
            else{
                header("Content-Type: application/json");
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "$e"
                ]);
            }
        }
    }
    else {
        header("Location: ../index.php");
        exit();
    }
?>