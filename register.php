<?php
    require "php/db/config.php";

    session_start();
    if (isset($_SESSION["cuenta_usuario"])){
        header("Location: index.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST"){
        $username = trim(strip_tags($_POST["user"]));
        $password = $_POST["password"];

        if (empty($username) || empty($password)){
            exit();
        }

        if (preg_match('/^(?!.*_{2,})[a-zA-Z0-9_]{3,16}$/', $username)){
            $password = password_hash($password, PASSWORD_BCRYPT);
            try{
                $sql = $conn->prepare("INSERT INTO usuarios(username, password, nickname) VALUES (?, ?, ?);");
                $sql->execute([$username, $password, $username]);
                header("Location: login.php?reg=1");
                exit();
            }
            catch(PDOException $e){
                $mensaje = "<span>Error:</span> El usuario ya existe";
            }
        }
        else{
            $mensaje = "<span>Error:</span> El usuario es muy corto, muy largo o contiene caracteres inválidos";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Tachibana</title>
    <link rel="stylesheet" href="styles/styles.css">
    <script src="js/register/verify.js" defer></script>
    <link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
    <?php include("resources/nav.php"); ?>
    <div class="auth-contenedor">
        <div class="contenido-menu">
            <h1 id="texto-centrado">Registrarse</h1>
            <form action="register.php" method="post" id="formulario">
                <div class="auth-campo">
                    <p>Usuario</p>
                    <input type="text" name="user" id="usernameF" placeholder="Nombre de usuario" required>
                </div>
                <div class="auth-campo">
                    <p>Contraseña</p>
                    <input type="password" name="password" placeholder="Contraseña" id="contraseña" required>
                </div>
                <div class="auth-campo">
                    <p>Repetir contraseña</p>
                    <input type="password" name="verifyPassword" placeholder="Repetir contraseña" id="repetirContraseña" required>
                </div>
                <div id="register-mensaje">
                    <?php
                        if (isset($mensaje)){
                            echo "<p id='formulario-mensaje'>$mensaje</p>";
                        }
                    ?>
                </div>
                <input type="submit" value="Registrarse">
            </form>
        </div>
        <p id="registrate">¿Tenés cuenta? Iniciá sesión <a href="login.php">acá</a></p>
    </div>
</body>
</html>