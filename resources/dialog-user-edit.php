<dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-editar-usuario">
    <div class="contenido-subir-formulario">
        <h2 class="contenido-setup-titulo">Modificar datos</h2>
        <div class="perfil-banner-parte1-modificado">
            <form id="formulario-editar-usuario">
                <div class="perfil-banner-parte1-fila">
                    <div class="perfil-banner-parte1-modificado-input">
                        <p>Nickname</p>
                        <input type="text" id="editar-usuario-nickname" value="<?= e($nickname) ?>" placeholder="Nickname...">
                    </div>
                    <div class="perfil-banner-parte1-modificado-input">
                        <p>Username</p>
                        <input type="text" id="editar-usuario-username" value="<?= e($nombre_usuario) ?>" placeholder="Username...">
                    </div>
                </div>

                <div class="perfil-banner-parte1-modificado-input">
                    <p>Descripción</p>
                    <textarea id="editar-usuario-descripcion" class="descripcion-input-perfil" placeholder="Descripción..." maxlength="500"><?= strip_tags($descripcion) ?></textarea>
                </div>

                <div class="perfil-banner-parte1-modificado-input">
                    <p>Avatar</p>
                    <div class="perfil-banner-parte1-modificado-input-avatar">
                        <div class="perfil-banner-parte1-modificado-input-avatar-preview">
                            <?= avatar_img($avatar, "id='editar-usuario-avatar-preview'") ?>
                            <p>Vista previa</p>
                        </div>
                        <input type="button" id="editar-usuario-avatar-eliminar" value="Eliminar avatar"<?= file_exists($avatar) ? '' : ' disabled' ?>>
                    </div>
                </div>

                <div class="perfil-banner-parte1-modificado-input perfil-banner-parte1-modificado-input-gap">
                    <input type="submit" value="Guardar cambios" id="editar-usuario-guardar">
                </div>
            </form>
        </div>
    </div>
</dialog>