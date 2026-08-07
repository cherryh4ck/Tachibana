<?php
    require "php/db/config.php";
    if (isset($_SESSION["cuenta_usuario"])){
        header("Location: index.php");
        exit();
    }

    if (isset($_GET["reg"])){
        $mensaje = "Usuario registrado, inicia sesión";
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST"){
        $username = trim($_POST["user"]);
        $password = $_POST["password"];

        if (empty($username) || empty($password)){
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
                    $_SESSION["error"] = 0;
                    header("Location: index.php");
                    exit();
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
    <title>Iniciar sesión - Tachibana</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="shortcut icon" href="favicon.ico" />

    <script src="js/login/sanidad.js" defer></script>
</head>
<body>
    <?php include("resources/nav.php"); ?>
    <div class="reglas-contenedor">
        <h1>Reglas de Tachibana</h1>
        <div class="regla">
            <div class="regla-espacio">
                <p>1.</p>
            </div>
            <div class="regla-texto">
                <p>Debes tener al menos 18 años para usar este sitio.</p>
            </div>   
        </div>
        <div class="regla">
            <div class="regla-espacio">
                <p>2.</p>
            </div>
            <div class="regla-texto">
                <p>El contenido ilegal está estrictamente prohibido.</p>
            </div>   
        </div>
        <div class="regla">
            <div class="regla-espacio">
                <p>3.</p>
            </div>
            <div class="regla-texto">
                <p>No se permite la publicación de información personal o doxxing.</p>
            </div>   
        </div>
        <div class="regla">
            <div class="regla-espacio">
                <p>4.</p>
            </div>
            <div class="regla-texto">
                <p>La automatización de actividades (como postear) está prohíbida si esta es usada para fines maliciosos.</p>
            </div>   
        </div>
        <div class="regla">
            <div class="regla-espacio">
                <p>5.</p>
            </div>
            <div class="regla-texto">
                <p>No se permite la publicación de imágenes inapropiadas (NSFW).</p>
            </div>   
        </div>
        <div class="regla">
            <div class="regla-espacio"></div>
            <div class="regla-texto">
                <p>Romper cualquiera de estas reglas resultará en la eliminación del post o en la suspensión de la cuenta.</p>
            </div>   
        </div>
        <h2>¡Feliz posteo!</h2>
    </div>
    <?php include("resources/footer.php"); ?>
</body>
</html>