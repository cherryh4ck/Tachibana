<?php
    $nav_rol = $_SESSION["cuenta_rol"] ?? null;
?>

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
            <a href="php/cuenta.php" class="nav-cuenta-link">
                <span class="nav-status-cuenta">
                    <span class="nav-username">Anónimo</span>
                </span>
                <img src="resources/avatar.png" alt="" class="nav-avatar">
            </a>
        <?php else:
            $avatar_propio = "resources/avatars/" . $_SESSION["cuenta_id"] . ".png";
        ?>
            <a href="php/cuenta.php" class="nav-cuenta-link">
                <span class="nav-status-cuenta">
                    <span class="nav-username"><?= e($_SESSION["cuenta_usuario"]) ?></span>
                    <?php if ($nav_rol): ?>
                        <span id="input-tag-<?= e($nav_rol) ?>" class="nav-rol"><?= strtoupper(e($nav_rol)) ?></span>
                    <?php endif; ?>
                </span>
                <?= avatar_img($avatar_propio, "class='nav-avatar'") ?>
            </a>
        <?php endif; ?>
    </div>
</nav>