<?php
    require "php/db/config.php";
    require "resources/parse_functions.php";

    if (!isset($_SESSION["cuenta_usuario"])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_SESSION["cuenta_rol"]) || !in_array($_SESSION["cuenta_rol"], ["admin", "mod"], true)) {
        header("Location: index.php");
        exit();
    }

    $es_admin = $_SESSION["cuenta_rol"] === "admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de moderación - Tachibana</title>
    <script>
        const cuentaId = <?= (int) $_SESSION["cuenta_id"] ?>;
        const cuentaRol = "<?= e($_SESSION["cuenta_rol"]) ?>";
        const esAdmin = <?= $es_admin ? "true" : "false" ?>;
    </script>
    <script src="js/notifications.js" defer></script>
    <script src="js/subir_modal.js" defer></script>
    <script src="js/mod-panel/core.js" defer></script>
    <script src="js/mod-panel/users.js" defer></script>
    <script src="js/mod-panel/posts.js" defer></script>
    <script src="js/mod-panel/tags.js" defer></script>
    <script src="js/mod-panel/notifications.js" defer></script>
    <script src="js/mod-panel/tachibana.js" defer></script>

    <link rel="stylesheet" href="styles/styles.css">
    <link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
    <?php
        include("resources/warning-bar.php");
        include("resources/nav.php");
    ?>
    <div class="modpanel">
        <div class="modpanel-header">
            <h1>Panel de moderación</h1>
            <span id="input-tag-<?= e($_SESSION["cuenta_rol"]) ?>" class="comentar-input-tag-op"><?= strtoupper(e($_SESSION["cuenta_rol"])) ?></span>
        </div>

        <div class="modpanel-tabs">
            <button type="button" class="modpanel-tab modpanel-tab-activa" data-tab="usuarios">Usuarios</button>
            <button type="button" class="modpanel-tab" data-tab="posts">Posts</button>
            <button type="button" class="modpanel-tab" data-tab="tags">Tags</button>
            <button type="button" class="modpanel-tab" data-tab="notificaciones">Notificaciones</button>
            <button type="button" class="modpanel-tab" data-tab="tachibana">Tachibana</button>
        </div>

        <section class="modpanel-panel modpanel-panel-activo" id="modpanel-usuarios">
            <div class="modpanel-toolbar">
                <input type="text" id="modpanel-usuarios-buscar" placeholder="Buscar por username o nickname...">
                <select id="modpanel-usuarios-rol">
                    <option value="">Todos los roles</option>
                    <option value="user">Usuarios</option>
                    <option value="mod">Moderadores</option>
                    <option value="admin">Administradores</option>
                </select>
                <select id="modpanel-usuarios-estado">
                    <option value="">Todos los estados</option>
                    <option value="ban">Suspendidos</option>
                    <option value="activo">Activos</option>
                </select>
            </div>
            <div class="modpanel-bulkbar" id="modpanel-usuarios-bulkbar" hidden>
                <label class="modpanel-checkbox">
                    <input type="checkbox" id="modpanel-usuarios-seleccionar-todos">
                    <span id="modpanel-usuarios-contador">0 seleccionados</span>
                </label>
                <div class="modpanel-bulkbar-acciones">
                    <button type="button" id="modpanel-usuarios-bulk-ban">Suspender</button>
                    <button type="button" id="modpanel-usuarios-bulk-unban">Desbanear</button>
                    <?php if ($es_admin): ?>
                        <button type="button" id="modpanel-usuarios-bulk-ascender">Ascender</button>
                    <?php endif; ?>
                    <button type="button" id="modpanel-usuarios-bulk-delete" class="modpanel-peligro">Eliminar</button>
                </div>
            </div>
            <div class="modpanel-lista" id="modpanel-usuarios-lista"></div>
            <p class="modpanel-vacio" id="modpanel-usuarios-vacio" hidden>No se encontraron usuarios.</p>
            <button type="button" class="modpanel-cargar-mas" id="modpanel-usuarios-cargar-mas" hidden>Cargar más</button>
        </section>

        <section class="modpanel-panel" id="modpanel-posts">
            <div class="modpanel-toolbar">
                <input type="text" id="modpanel-posts-buscar" placeholder="Buscar por título...">
                <select id="modpanel-posts-categoria">
                    <option value="">Todas las categorías</option>
                    <option value="any">/any/</option>
                    <option value="anime">/anime/</option>
                    <option value="manga">/manga/</option>
                    <option value="games">/games/</option>
                    <option value="pol">/pol/</option>
                    <option value="tech">/tech/</option>
                    <option value="music">/music/</option>
                    <option value="movie">/movie/</option>
                    <option value="coding">/coding/</option>
                </select>
                <select id="modpanel-posts-estado">
                    <option value="">Todos los estados</option>
                    <option value="sticky">Fijados</option>
                    <option value="archivado">Archivados</option>
                    <option value="baneado">Baneados</option>
                </select>
            </div>
            <div class="modpanel-bulkbar" id="modpanel-posts-bulkbar" hidden>
                <label class="modpanel-checkbox">
                    <input type="checkbox" id="modpanel-posts-seleccionar-todos">
                    <span id="modpanel-posts-contador">0 seleccionados</span>
                </label>
                <div class="modpanel-bulkbar-acciones">
                    <?php if ($es_admin): ?>
                        <button type="button" id="modpanel-posts-bulk-sticky">Fijar / Desfijar</button>
                    <?php endif; ?>
                    <button type="button" id="modpanel-posts-bulk-archive">Archivar / Desarchivar</button>
                    <button type="button" id="modpanel-posts-bulk-ban">Banear</button>
                    <button type="button" id="modpanel-posts-bulk-delete" class="modpanel-peligro">Eliminar</button>
                </div>
            </div>
            <div class="modpanel-lista" id="modpanel-posts-lista"></div>
            <p class="modpanel-vacio" id="modpanel-posts-vacio" hidden>No se encontraron posts.</p>
            <button type="button" class="modpanel-cargar-mas" id="modpanel-posts-cargar-mas" hidden>Cargar más</button>
        </section>

        <section class="modpanel-panel" id="modpanel-tags">
            <div class="modpanel-toolbar">
                <input type="text" id="modpanel-tags-buscar" placeholder="Buscar tag...">
            </div>
            <div class="modpanel-bulkbar" id="modpanel-tags-bulkbar" hidden>
                <span id="modpanel-tags-contador">0 seleccionados</span>
                <div class="modpanel-bulkbar-acciones">
                    <button type="button" id="modpanel-tags-bulk-delete" class="modpanel-peligro">Eliminar</button>
                </div>
            </div>
            <div class="modpanel-tags-lista" id="modpanel-tags-lista"></div>
            <p class="modpanel-vacio" id="modpanel-tags-vacio" hidden>No se encontraron tags.</p>
            <button type="button" class="modpanel-cargar-mas" id="modpanel-tags-cargar-mas" hidden>Cargar más</button>
        </section>

        <section class="modpanel-panel" id="modpanel-notificaciones">
            <form id="formulario-modpanel-notif" class="modpanel-notif-form">
                <div class="modpanel-autocomplete">
                    <p>Usuario</p>
                    <input type="text" id="modpanel-notif-buscar" placeholder="Buscar por username o nickname...">
                    <div class="modpanel-autocomplete-resultados" id="modpanel-notif-resultados" hidden></div>
                    <input type="hidden" id="modpanel-notif-user-id">
                    <div class="modpanel-seleccionado" id="modpanel-notif-seleccionado" hidden></div>
                </div>

                <p>Ícono</p>
                <div class="modpanel-icon-picker">
                    <button type="button" class="modpanel-icon-opcion modpanel-icon-activo" data-tipo="mod_message" title="Info"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg></button>
                    <button type="button" class="modpanel-icon-opcion" data-tipo="unban" title="Confirmación"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.3 2.3L16 9.5"/></svg></button>
                    <button type="button" class="modpanel-icon-opcion" data-tipo="post_banned" title="Advertencia"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.8 21 19.5H3z"/><path d="M12 9.8v4.6"/><path d="M12 17.2h.01"/></svg></button>
                    <button type="button" class="modpanel-icon-opcion" data-tipo="ban" title="Prohibición"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M6.2 6.2l11.6 11.6"/></svg></button>
                    <button type="button" class="modpanel-icon-opcion" data-tipo="post_deleted" title="Eliminación"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6.5 7 7.3 19a1 1 0 0 0 1 1h7.4a1 1 0 0 0 1-1L17.5 7"/></svg></button>
                </div>
                <input type="hidden" id="modpanel-notif-tipo" value="mod_message">

                <p>Mensaje</p>
                <textarea id="modpanel-notif-mensaje" maxlength="300" rows="3" placeholder="Escribí el mensaje de la notificación..." required></textarea>

                <input type="submit" value="Enviar notificación" id="modpanel-notif-enviar">
            </form>
        </section>

        <section class="modpanel-panel" id="modpanel-tachibana">
            <div class="modpanel-tachibana">
                <p id="modpanel-tachibana-version">?</p>
            </div>
        </section>
    </div>

    <dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-modpanel-confirmar">
        <div class="contenido-subir-formulario">
            <h2 class="contenido-setup-titulo" id="modpanel-confirmar-titulo">Confirmar acción</h2>
            <form id="formulario-modpanel-confirmar">
                <p id="modpanel-confirmar-mensaje"></p>
                <input type="submit" value="Confirmar" id="modpanel-confirmar-enviar">
            </form>
        </div>
    </dialog>

    <dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-modpanel-ban-usuarios">
        <div class="contenido-subir-formulario">
            <h2 class="contenido-setup-titulo">Suspender usuarios</h2>
            <form id="formulario-modpanel-ban-usuarios">
                <div class="contenido-subir-formulario-fila1">
                    <div class="contenido-subir-formulario-fila1-input-allspace">
                        <p>Motivo del baneo</p>
                        <input type="text" id="modpanel-ban-usuarios-motivo" placeholder="...">
                    </div>
                </div>
                <div class="contenido-subir-formulario-fila1">
                    <div class="contenido-subir-formulario-fila1-input-allspace">
                        <p>Baneado hasta</p>
                        <input type="datetime-local" id="modpanel-ban-usuarios-expiracion">
                    </div>
                </div>
                <input type="submit" value="Suspender" id="modpanel-ban-usuarios-enviar">
            </form>
            <script src="js/modal-validation/banDateModPanel.js" defer></script>
        </div>
    </dialog>

    <dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-modpanel-ban-posts">
        <div class="contenido-subir-formulario">
            <h2 class="contenido-setup-titulo">Banear posts</h2>
            <form id="formulario-modpanel-ban-posts">
                <div class="contenido-subir-formulario-fila1">
                    <div class="contenido-subir-formulario-fila1-input-allspace">
                        <p>Motivo del baneo</p>
                        <input type="text" id="modpanel-ban-posts-motivo" placeholder="...">
                    </div>
                </div>
                <div class="contenido-subir-formulario-fila1-input-checkbox">
                    <input type="checkbox" id="modpanel-ban-posts-recursos" checked>
                    <label for="modpanel-ban-posts-recursos">También eliminar imagen y miniatura</label>
                </div>
                <div class="contenido-subir-formulario-fila1-input-checkbox">
                    <input type="checkbox" id="modpanel-ban-posts-suspender">
                    <label for="modpanel-ban-posts-suspender">Suspender la cuenta del autor (12hs)</label>
                </div>
                <input type="submit" value="Banear" id="modpanel-ban-posts-enviar">
            </form>
        </div>
    </dialog>

    <dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-modpanel-editar-usuario">
        <div class="contenido-subir-formulario">
            <h2 class="contenido-setup-titulo">Modificar datos</h2>
            <div class="perfil-banner-parte1-modificado">
                <form id="formulario-modpanel-editar-usuario">
                    <div class="perfil-banner-parte1-fila">
                        <div class="perfil-banner-parte1-modificado-input">
                            <p>Nickname</p>
                            <input type="text" id="modpanel-editar-nickname" placeholder="Nickname...">
                        </div>
                        <div class="perfil-banner-parte1-modificado-input">
                            <p>Username</p>
                            <input type="text" id="modpanel-editar-username" placeholder="Username...">
                        </div>
                    </div>

                    <div class="perfil-banner-parte1-modificado-input">
                        <p>Descripción</p>
                        <textarea id="modpanel-editar-descripcion" class="descripcion-input-perfil" placeholder="Descripción..." maxlength="500"></textarea>
                    </div>

                    <div class="perfil-banner-parte1-modificado-input">
                        <p>Avatar</p>
                        <div class="perfil-banner-parte1-modificado-input-avatar">
                            <div class="perfil-banner-parte1-modificado-input-avatar-preview">
                                <img src="resources/avatar.png" alt="" id="modpanel-editar-avatar-preview">
                                <p>Vista previa</p>
                            </div>
                            <input type="button" id="modpanel-editar-avatar-eliminar" value="Eliminar avatar">
                        </div>
                    </div>

                    <div class="perfil-banner-parte1-modificado-input perfil-banner-parte1-modificado-input-gap">
                        <input type="submit" value="Guardar cambios" id="modpanel-editar-guardar">
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <?php include("resources/dialog-upload.php"); ?>
    <?php include("resources/footer.php"); ?>
</body>
</html>