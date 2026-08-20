<?php
    require "php/db/config.php";
    require "resources/parse_functions.php";

    if (isset($_GET["id"])) {
        if (!is_numeric($_GET["id"])) {
            $_SESSION["error"] = 2;
            header("Location: error.php");
            exit();
        }
        $id_perfil = (int) $_GET["id"];
    } elseif (isset($_SESSION["cuenta_usuario"])) {
        $id_perfil = (int) $_SESSION["cuenta_id"];
    } else {
        header("Location: login.php");
        exit();
    }

    $es_el_dueño = isset($_SESSION["cuenta_id"]) && $id_perfil === (int) $_SESSION["cuenta_id"];

    if (isset($_GET["editar"])) {
        $modo = "editar";
    } elseif (isset($_GET["seguridad"])) {
        $modo = "seguridad";
    } else {
        $modo = "ver";
    }

    if ($modo !== "ver" && !$es_el_dueño) {
        header("Location: index.php");
        exit();
    }

    try {
        $sql = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $sql->execute([$id_perfil]);
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION["error"] = 2;
        header("Location: error.php");
        exit();
    }

    if (!$usuario) {
        $_SESSION["error"] = 2;
        header("Location: error.php");
        exit();
    }

    $nombre_usuario         = $usuario["username"];
    $nickname               = $usuario["nickname"];
    $descripcion            = $usuario["descripcion"];
    $rol                    = $usuario["rol"];
    $fecha_creacion         = $usuario["fecha_creacion"];
    $ultima_actividad       = $usuario["ult_act"];
    $ultima_actividad_activo = $usuario["ult_act_activo"];
    $avatar                 = "resources/avatars/" . $id_perfil . ".png";

    if (esta_baneado($conn, $id_perfil)) {
        $esta_ban = true;
    }
    else {
        $esta_ban = false;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($nickname) ?> - Tachibana</title>
    <script src="js/notifications.js" defer></script>
    <script src="js/subir_modal.js" defer></script>

    <link rel="stylesheet" href="styles/styles.css">
    <link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
    <?php 
        include("resources/warning-bar.php");
        include("resources/nav.php"); ?>
    <header>
        <div class="perfil-div">
            <div class="perfil-banner">
                <?php if ($modo === "ver"): ?>
                    <div class="perfil-banner-parte1">
                        <?= avatar_img($avatar) ?>
                        <div class="perfil-info">
                            <div class="perfil-info-nickname-tags">
                                <p><b><?= e($nickname) ?></b></p>
                                <span id="perfil-tag-mod" class="comentar-input-tag-op"<?= $rol === "mod" ? "" : " style='display:none;'" ?>>MOD</span>
                                <span id="perfil-tag-admin" class="comentar-input-tag-op"<?= $rol === "admin" ? "" : " style='display:none;'" ?>>ADMIN</span>
                                <span id="input-tag-ban" class="comentar-input-tag-op"<?= $esta_ban ? "" : " style='display:none;'" ?>>BAN</span>
                            </div>
                            <p id="contenido-perfil-bloque-info-username">@<?= e($nombre_usuario) ?></p>
                            <div class="perfil-info-avanzada">
                                <?php if ($ultima_actividad_activo == 1): ?>
                                    <p>Se unió hace <?= calcular_tiempo($fecha_creacion) ?> <span id="viñeta">•</span> Última vez hace <?= calcular_tiempo($ultima_actividad) ?></p>
                                <?php else: ?>
                                    <p>Se unió hace <?= calcular_tiempo($fecha_creacion) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($es_el_dueño): ?>
                        <div class="perfil-banner-parte2">
                            <button onclick="window.location.href='perfil.php?editar=1'">Editar perfil</button>
                            <button onclick="window.location.href='perfil.php?seguridad=1'">Administrar seguridad</button>
                            <button onclick="window.location.href='php/db/logout.php'" id="boton-cerrar-sesion">Cerrar sesión</button>
                        </div>
                    <?php endif; ?>

                <?php elseif ($modo === "editar"): ?>
                    <div class="perfil-banner-parte1-modificado">
                        <script src="js/perfil/editar.js" defer></script>
                        <script src="js/perfil/ult_act.js" defer></script>
                        <script src="js/perfil/caracteres.js" defer></script>
                        <form action="php/account/editar.php" method="POST" enctype="multipart/form-data" id="formulario-editar-perfil" onkeydown="if (event.keyCode === 13 && event.target.tagName !== 'TEXTAREA') {return false;}">
                            <input type="hidden" name="ultima-actividad" id="ultima-actividad-hidden" value="<?= $ultima_actividad_activo == 1 ? '1' : '0' ?>">

                            <div class="perfil-banner-parte1-fila">
                                <div class="perfil-banner-parte1-modificado-input">
                                    <p>Nickname</p>
                                    <input type="text" name="nickname" id="nickname-input" value="<?= e($nickname) ?>" placeholder="Nickname...">
                                </div>
                                <div class="perfil-banner-parte1-modificado-input perfil-banner-parte1-modificado-input-username">
                                    <p>Username</p>
                                    <input type="text" value="<?= e($nombre_usuario) ?>" disabled>
                                </div>
                            </div>

                            <div class="perfil-banner-parte1-modificado-input">
                                <p>Descripción</p>
                                <textarea name="descripcion" id="descripcion-input" class="descripcion-input-perfil" placeholder="Descripción..." maxlength="400"><?= strip_tags($descripcion) ?></textarea>
                                <p style="display: none;" id="perfil-banner-parte1-modificado-input-caracteres">ad</p>
                            </div>

                            <div class="perfil-banner-parte1-modificado-input">
                                <p>Avatar</p>
                                <div class="perfil-banner-parte1-modificado-input-avatar">
                                    <div class="perfil-banner-parte1-modificado-input-avatar-preview">
                                        <?= avatar_img($avatar, "id='avatar-img'") ?>
                                        <p>80px</p>
                                    </div>
                                    <div class="perfil-banner-parte1-modificado-input-avatar-preview">
                                        <?= avatar_img($avatar, "id='avatar-img2' class='perfil-banner-parte1-modificado-input-avatar-preview-chiquito'") ?>
                                        <p>50px</p>
                                    </div>
                                    <input type="file" accept=".png, .jpg, .jpeg" name="avatar" id="avatar-file">
                                </div>
                            </div>

                            <div class="perfil-banner-parte1-modificado-input">
                                <p>Privacidad</p>
                            </div>
                            <div class="perfil-banner-parte1-checkbox">
                                <input type="checkbox" id="ultima-actividad-checkbox" <?= $ultima_actividad_activo == 1 ? 'checked' : '' ?>>
                                <label for="anonimo">Mostrar última actividad</label>
                            </div>

                            <div class="contenido-subir-formulario-error perfil-editar-mensaje">
                                <!-- div para mostrar errores / avisos mediante js/archivos.js -->
                                <p style="display: none;" id="mensaje-error"><span>Error al editar el perfil:</span> Test test</p>
                                <p style="display: none;" id="mensaje-aviso"><span id="mensaje-aviso2">Aviso:</span> El ancho y la altura del avatar no coinciden, por lo que puede verse estirado.</p>
                            </div>

                            <div class="perfil-banner-parte1-modificado-input perfil-banner-parte1-modificado-input-gap">
                                <input type="submit" value="Guardar cambios" id="guardar-cambios" disabled>
                                <input type="button" value="Volver" onclick="window.location.href='perfil.php'">
                            </div>
                        </form>
                    </div>

                <?php else:
                    $errores_seguridad = [
                        1 => "Los campos están vacíos",
                        2 => "Las contraseñas nuevas no coinciden",
                        3 => "La contraseña nueva debe tener entre 6 y 72 caracteres",
                        4 => "La contraseña actual es incorrecta",
                    ];
                ?>
                    <div class="perfil-banner-parte1-modificado">
                        <script src="js/perfil/seguridad.js" defer></script>
                        <form action="php/account/seguridad.php" method="POST" id="formulario-seguridad-perfil" onkeydown="if (event.keyCode === 13 && event.target.tagName !== 'TEXTAREA') {return false;}">
                            <div class="perfil-banner-parte1-modificado-input">
                                <p>Contraseña actual</p>
                                <input type="password" name="actual" id="actual-input" placeholder="Contraseña actual...">
                            </div>
                            <div class="perfil-banner-parte1-modificado-input">
                                <p>Contraseña nueva</p>
                                <input type="password" name="nueva" id="nueva-input" placeholder="Contraseña nueva...">
                            </div>
                            <div class="perfil-banner-parte1-modificado-input">
                                <p>Repetir contraseña nueva</p>
                                <input type="password" name="repetir" id="repetir-input" placeholder="Repetir contraseña nueva...">
                            </div>

                            <div class="contenido-subir-formulario-error perfil-editar-mensaje">
                                <p style="display: none;" id="mensaje-error"><span>Error al cambiar la contraseña:</span> Test test</p>
                                <p style="display: none;" id="mensaje-aviso"><span id="mensaje-aviso2">Aviso:</span> La contraseña se cambió correctamente.</p>
                            </div>

                            <?php if (isset($_GET["error"]) && isset($errores_seguridad[$_GET["error"]])): ?>
                                <script>window.addEventListener('DOMContentLoaded', () => { mostrarErrorSeguridad('<?= e($errores_seguridad[$_GET["error"]]) ?>'); });</script>
                            <?php endif; ?>
                            <?php if (isset($_GET["ok"])): ?>
                                <script>window.addEventListener('DOMContentLoaded', () => { mostrarAvisoSeguridad(); });</script>
                            <?php endif; ?>

                            <div class="perfil-banner-parte1-modificado-input perfil-banner-parte1-modificado-input-gap">
                                <input type="submit" value="Cambiar contraseña" id="guardar-cambios-seguridad">
                                <input type="button" value="Volver" onclick="window.location.href='perfil.php'">
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($modo === "ver"): ?>
            <div class="perfil-div perfil-div-separacion">
                <div class="perfil-descripcion">
                    <p id="perfil-descripcion-texto">Descripción</p>
                    <div id="perfil-descripcion-contenido"<?= $esta_ban ? " style='display:none;'" : "" ?>>
                        <?php if (!$esta_ban): ?>
                            <?php if (!empty($descripcion)): ?>
                                <?= formatear_descripcion($descripcion) ?>
                            <?php else: ?>
                                <p>No hay descripción.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <p id="perfil-descripcion-texto-suspendido"<?= $esta_ban ? "" : " style='display:none;'" ?>>Este usuario se encuentra suspendido.</p>
                </div>
            </div>

            <?php if (($_SESSION['cuenta_rol'] === 'admin' || $_SESSION['cuenta_rol'] === 'mod') && !$es_el_dueño):
                $rango_actor = rango_rol($_SESSION['cuenta_rol']);
                $rango_objetivo = rango_rol($rol);
                $puede_banear = $rango_actor > $rango_objetivo;
                $puede_ascender = $_SESSION['cuenta_rol'] === 'admin' && ($rol === "user" || $rol === "mod");
            ?>
                <?php if ($puede_banear):
                    require "resources/dialog-user-ban.php";
                    require "resources/dialog-user-delete.php";
                    echo "<script src='js/perfil/modal-ban.js' defer></script>";
                    echo "<script src='js/perfil/modal-delete.js' defer></script>";
                endif; ?>
                <div class="perfil-div perfil-div-separacion">
                    <div class="perfil-descripcion">
                        <p id="perfil-descripcion-texto">Acciones administrativas (<?php echo $_SESSION['cuenta_rol']; ?>)</p>
                        <div class="perfil-descripcion-acciones">
                            <?php if ($puede_banear): ?>
                                <?php if ($esta_ban): ?>
                                <button id="boton-bloquear-usuario" data-id="<?= $id_perfil ?>" data-mode="unban">Desbanear usuario</button>
                                <?php else: ?>
                                <button id="boton-bloquear-usuario" data-id="<?= $id_perfil ?>" data-mode="ban">Suspender usuario</button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($puede_ascender): ?>
                                <?php if ($rol === "user"): ?>
                                <button id="boton-ascender-usuario" data-id="<?= $id_perfil ?>" data-objetivo="mod">Ascender a moderador</button>
                                <?php elseif ($rol === "mod"): ?>
                                <button id="boton-ascender-usuario" data-id="<?= $id_perfil ?>" data-objetivo="admin">Ascender a administrador</button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <button id="boton-delete-usuario" data-id="<?= $id_perfil ?>">Eliminar usuario</button>
                            <button onclick="window.location.href='php/db/logout.php'" id="boton-cerrar-sesion">Modificar datos</button>
                        </div>
                    </div>
                </div>
                <?php if ($puede_banear): echo "<script src='js/admin/modify-user.js' defer></script>"; endif; ?>
                <?php if ($puede_ascender): echo "<script src='js/admin/promote-user.js' defer></script>"; endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php include("resources/dialog-upload.php"); ?>
    </header>
    <?php include("resources/footer.php"); ?>
</body>
</html>