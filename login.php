<?php
    require "php/db/config.php";
    if (isset($_SESSION["cuenta_usuario"])){
        header("Location: index.php");
    }

    if (isset($_GET["reg"])){
        $mensaje = "Usuario registrado, inicia sesión";
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST"){
        $username = trim($_POST["user"]);
        $password = $_POST["password"];

        if (empty($user) || empty($password)){
            $mensaje = "<span>Error:</span> Los campos están vacios.";
        }
        else{
            try{
                $sql = $conn->prepare("SELECT * FROM usuarios WHERE username = ?");
                $sql->execute([$username]);
                $fetch = $sql->fetch(PDO::FETCH_ASSOC);

                if ($fetch && password_verify($password, $fetch["password"])){
                    // crear auth cookie
                    $auth_cookie = bin2hex(random_bytes(128));
                    $sql = $conn->prepare("UPDATE usuarios SET auth_cookie = ? WHERE id = ?");
                    $sql->execute([$auth_cookie, $fetch["id"]]);

                    $ult_act_activado = (int)$fetch["ult_act_activo"];

                    setcookie("ult_act", $ult_act_activado, time() + (86400 * 30), "/"); 
                    setcookie("auth", $auth_cookie, time() + (86400 * 30), "/"); 
                    $_SESSION["cuenta_id"] = $fetch["id"];
                    $_SESSION["cuenta_usuario"] = $fetch["username"];
                    $_SESSION["cuenta_rol"] = $fetch["rol"];
                    header("Location: index.php");
                }
                else{
                    $mensaje = "<span>Error:</span> Usuario o contraseña incorrecta";
                }
            }
            catch(PDOException $e){
                $mensaje = "<span>Error:</span> Ha ocurrido un error, intente más tarde";
                if ($debug == 1) {
                    echo $e;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="shortcut icon" href="favicon.ico" />

    <script src="js/login/sanidad.js" defer></script>
</head>
<body>
    <?php include("resources/nav.php"); ?>
    <div class="contenido-menu">
        <form action="login.php" method="post" id="formulario-login">
            <p id="texto-centrado">Iniciar sesión</p>
            <input type="text" name="user" placeholder="Nombre de usuario" id="user-input" required>
            <input type="password" name="password" placeholder="Contraseña" id="password-input" required>
            <?php
                if (isset($mensaje)){
                    echo "<p id='formulario-mensaje2'>$mensaje</p>";
                }
                echo "<p id='formulario-mensaje' style='display: none;'></p>";
            ?>
            <input type="submit" value="Iniciar sesión">
        </form>
        <p id="registrate">¿No tenés cuenta? Registrate <a href="register.php">acá</a></p>
    </div>
</body>
</html>
