<?php
    require "php/db/config.php";
    require "resources/parse_functions.php";
    /*
        IMPORTANTE:
          Puedes modificar este archivo como se te antoje. 
          No necesariamente estas reglas son definitivas.
          Como tal, no deberías usar el contenido de este archivo, es solo una idea.
    */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reglas - Tachibana</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="shortcut icon" href="favicon.ico" />
    <script src="js/notifications.js" defer></script>
</head>
<body>
    <?php include("resources/warning-bar.php"); 
        include("resources/nav.php"); ?>
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