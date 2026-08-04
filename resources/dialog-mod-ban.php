<dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-mod-ban">
    <div class="contenido-subir-formulario">
        <h2 class="contenido-setup-titulo">Banear post</h2>
        <script src="js/admin/modify-post.js" defer></script>
        <form id="formulario-ban">
            <p>Al banear este post ciertos apartados (como los comentarios) seguirán siendo visibles. Para eliminar completamente el post, utiliza la opción de eliminar post.</p>
            <br>
            <p>El motivo se mostrará en la descripción.</p>
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input-allspace">
                    <p>Motivo del baneo</p>
                    <input type="text" name="motivo" id="ban-motivo" placeholder="...">
                </div>
            </div>
            <div class="contenido-subir-formulario-fila1-input-checkbox">
                <input type="checkbox" name="eliminar_recursos" id="subir-eliminar-recursos" checked>
                <label for="subir-eliminar-recursos">También eliminar imagen y miniatura</label>
            </div>
            <div class="contenido-subir-formulario-fila1-input-checkbox">
                <input type="checkbox" name="suspender_cuenta" id="subir-suspender-cuenta">
                <label for="subir-suspender-cuenta">Suspender la cuenta del autor (12hs)</label>
            </div>
            <div class="contenido-subir-formulario-error">
                <p style="display: none;" id="mensaje-error"><span>Error al instalar:</span> Test test</p>
            </div>
            <input type="submit" value="Banear" id="setup-enviar">
        </form>
    </div>
</dialog>