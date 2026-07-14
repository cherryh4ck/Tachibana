<dialog style="display: none;" class="contenido-subir contenido-setup" id="dialog-setup">
    <div class="contenido-subir-formulario">
        <h2 class="contenido-setup-titulo">Tachibana</h2>
        <script src="js/setup.js" defer></script>
        <form action="php/setup.php" method="POST" id="formulario-setup">
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input">
                    <p>Host de la DB</p>
                    <input type="text" name="host" id="setup-host" placeholder="localhost" required>
                </div>
                <div class="contenido-subir-formulario-fila1-input">
                    <p>Puerto de la DB</p>
                    <input type="text" name="puerto" id="setup-puerto" placeholder="3306" required>
                </div>
            </div>
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input">
                    <p>Usuario de la DB</p>
                    <input type="text" name="usuario" id="setup-usuario" placeholder="root" required>
                </div>
                <div class="contenido-subir-formulario-fila1-input">
                    <p>Password de la DB</p>
                    <input type="password" name="password" id="setup-password" placeholder="Contraseña...">
                </div>
            </div>
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input-allspace">
                    <p>Nombre de la DB</p>
                    <input type="text" name="database" id="setup-database" placeholder="tachibana" value="tachibana" required>
                </div>
            </div>
            <div class="contenido-subir-formulario-fila1-input-checkbox">
                <input type="checkbox" name="cuenta_obligatoria" id="setup-cuenta-obligatoria" checked>
                <label for="setup-cuenta-obligatoria">Requerir cuenta para poder postear</label>
            </div>
            <div class="contenido-subir-formulario-error">
                <p style="display: none;" id="mensaje-error"><span>Error al instalar:</span> Test test</p>
            </div>
            <input type="submit" value="Instalar" id="setup-enviar">
        </form>
    </div>
</dialog>
<script>
    const dialogSetup = document.getElementById("dialog-setup");
    dialogSetup.showModal();
    dialogSetup.style.display = "flex";
</script>
