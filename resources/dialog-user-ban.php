<dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-user-ban">
    <div class="contenido-subir-formulario">
        <h2 class="contenido-setup-titulo">Banear usuario</h2>
        <form id="formulario-ban">
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input-allspace">
                    <p>Motivo del baneo</p>
                    <input type="text" name="motivo" id="ban-motivo" placeholder="...">
                </div>
            </div>
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input-allspace">
                    <p>Baneado hasta</p>
                    <input type="datetime-local" name="fecha_expiracion" id="ban-fecha-expiracion">
                </div>
            </div>
            <div class="contenido-subir-formulario-error">
                <p style="display: none;" id="mensaje-error"><span>Error al instalar:</span> Test test</p>
            </div>
            <input type="submit" value="Banear" id="banear-enviar">
        </form>
    </div>
</dialog>