<nav>
    <p id="nav-logo">Tachibana</p>
    <ul>
        <li><a href="index.php?pag=1">Inicio</a></li>
        <?php if (isset($_SESSION["cuenta_usuario"])): ?>
            <li><a href="#" id="subir-boton-modal">Publicar</a></li>
        <?php endif; ?>
        <li><a href="perfiles.php">Usuarios</a></li>
    </ul>
    <div class="nav-cuenta">
        <?php if (!isset($_SESSION["cuenta_usuario"])): ?>
            <a href="php/cuenta.php" id="cuenta">Anónimo</a>
            <img src="resources/avatar.png" alt="">
        <?php else:
            $avatar_propio = "resources/avatars/" . $_SESSION["cuenta_id"] . ".png";
        ?>
            <a href="php/cuenta.php" id="cuenta"><?= e($_SESSION["cuenta_usuario"]) ?></a>
            <?= avatar_img($avatar_propio) ?>
        <?php endif; ?>
    </div>
</nav>