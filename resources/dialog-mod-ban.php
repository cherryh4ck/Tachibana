<dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-mod-ban">
    <div class="contenido-subir-formulario">
        <h2 class="contenido-setup-titulo">Banear post</h2>
        <script src="js/admin/modify-post.js" defer></script>
        <form action="php/setup.php" method="POST" id="formulario-setup">
            <input type="hidden" name="postID" value="<?php echo $id; ?>">
            <p>Banear un post hará que este siga siendo visible, sin embargo los datos del post serán ocultados.</p>
            <br>
            <p>El motivo se mostrará en la descripción.</p>
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input-allspace">
                    <p>Motivo del baneo</p>
                    <input type="text" name="motivo" id="setup-motivo" placeholder="...">
                </div>
            </div>
            <div class="contenido-subir-formulario-error">
                <p style="display: none;" id="mensaje-error"><span>Error al instalar:</span> Test test</p>
            </div>
            <input type="submit" value="Banear" id="setup-enviar">
        </form>
    </div>
</dialog>