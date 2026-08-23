<?php
    $nav_rol = $_SESSION["cuenta_rol"] ?? null;
    $nav_actual = basename($_SERVER["PHP_SELF"]);
?>

<nav>
    <p id="nav-logo">Tachibana</p>
    <ul>
        <li>
            <a href="index.php?pag=1" class="nav-link <?= $nav_actual == "index.php" ? "nav-link-activo" : "" ?>">
                <svg class="nav-link-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 4l9 6.5"/><path d="M5.5 9.5V19a1 1 0 0 0 1 1H10v-5.5h4V20h3.5a1 1 0 0 0 1-1V9.5"/></svg>
                <span>Inicio</span>
            </a>
        </li>
        <?php if (isset($_SESSION["cuenta_usuario"])): ?>
            <li>
                <a href="#" id="subir-boton-modal" class="nav-link nav-link-publicar">
                    <svg class="nav-link-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    <span>Publicar</span>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a href="perfiles.php" class="nav-link <?= $nav_actual == "perfiles.php" ? "nav-link-activo" : "" ?>">
                <svg class="nav-link-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19.5c0-3 2.5-5.2 5.5-5.2s5.5 2.2 5.5 5.2"/><path d="M15.3 8.4a3.2 3.2 0 1 1 0 6.1"/><path d="M15.3 14.5c2.6.3 4.7 2.4 4.7 5"/></svg>
                <span>Usuarios</span>
            </a>
        </li>
    </ul>
    <div class="nav-cuenta">
        <?php if (isset($_SESSION["cuenta_usuario"])): ?>
            <div class="nav-bell" id="nav-bell">
                <button type="button" id="nav-bell-button" class="nav-bell-button" aria-haspopup="true" aria-expanded="false" aria-label="Notificaciones">
                    <svg class="nav-bell-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 0 1 12 0c0 3.2 1 4.8 1.8 5.8.5.6.1 1.5-.6 1.5H4.8c-.7 0-1.1-.9-.6-1.5C5 13.8 6 12.2 6 9Z"/><path d="M9.5 18a2.5 2.5 0 0 0 5 0"/></svg>
                    <span id="nav-bell-badge" class="nav-bell-badge" hidden></span>
                </button>
                <div id="nav-bell-panel" class="nav-bell-panel" hidden>
                    <div class="nav-bell-panel-header">
                        <h4>Notificaciones</h4>
                        <button type="button" id="nav-bell-mark-all" class="nav-bell-mark-all">Marcar todo como leído</button>
                    </div>
                    <div id="nav-bell-list" class="nav-bell-list">
                        <p class="nav-bell-empty">No tenés notificaciones.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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

<?php if (isset($_SESSION["cuenta_usuario"])): ?>
    <script src="js/notification-bell.js" defer></script>
<?php endif; ?>