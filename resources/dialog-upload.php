<dialog style="display: none;" class="contenido-subir">
    <script src="js/archivos.js" type="module" defer></script>
    <script src="js/tags.js" type="module" defer></script>
    <img src="" alt="" id="image-preview" style="display: none;">
    <div class="contenido-subir-formulario">
        <form action="php/subida.php" method="POST" enctype="multipart/form-data" id="formulario-subir" onkeydown="if (event.keyCode === 13 && event.target.tagName !== 'TEXTAREA') {return false;}">
            <input type="hidden" name="tags" id="tags-value" value="">
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input">
                    <p>Título</p>
                    <input type="text" name="titulo" id="titulo-input" placeholder="Título del post..." required>
                </div> 
                <div class="contenido-subir-formulario-fila1-input">
                    <p>Categoría</p>
                    <select name="categoria" id="categoria-input" size="1">
                        <option value="1">General - /any/</option>
                        <option value="2">Anime - /anime/</option>
                        <option value="3">Manga - /manga/</option>
                        <option value="4">Videojuegos - /games/</option>
                        <option value="5">Política - /pol/</option>
                        <option value="6">Tecnología - /tech/</option>
                        <option value="7">Música - /music/</option>
                        <option value="8">Películas - /movie/</option>
                        <option value="9">Programación - /coding/</option>
                    </select>
                </div> 
            </div>
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input-allspace">
                    <p>Descripción</p>
                    <textarea name="descripcion" id="descripcion-input" placeholder="Descripción del post..." rows="7" cols="60"></textarea>
                </div>
            </div>
            <div class="contenido-subir-formulario-fila1-input-checkbox">
                <input type="checkbox" name="anonimo" id="anonimo-checkbox">
                <label for="anonimo">Publicar de forma anónima</label>
            </div>
            <div class="contenido-subir-formulario-fila1">
                <div class="contenido-subir-formulario-fila1-input">
                    <p>Tags</p>
                    <div class="contenido-subir-formulario-fila1-input-tags" id="insert-tags">
                        <p id="no-hay-tags">Añade un tag...</p>
                    </div>
                </div>
                <div class="contenido-subir-formulario-fila1-input">
                    <p>Insertar tags</p>
                    <input type="text" id="tags-input" placeholder="Tag... (máx. 4)">
                </div>
            </div>
            <div class="contenido-subir-formulario-fila-subir">
                <input type="file" accept=".png, .jpg, .jpeg, .gif, .jfif, .webp" name="archivo" id="archivo-file" class="subir-archivo">
                <div class="contenido-subir-formulario-fila-subir-textos">
                    <p id="imagen-texto">Imagen no seleccionada</p>
                    <div class="contenido-subir-formulario-fila-subir-textos-estado">
                        <p id="imagen-tamano"></p>
                        <p id="imagen-res"></p>
                    </div>
                </div>
            </div>
            <div class="contenido-subir-formulario-error">
                <!-- div para mostrar errores / avisos mediante js/archivos.js -->
                <p style="display: none;" id="mensaje-error"><span>Error al subir la imagen:</span> Test test</p>
                <p style="display: none;" id="mensaje-aviso"><span id="mensaje-aviso2">Aviso:</span> Tu ID de usuario se seguirá guardando en la base de datos como identificación, pero esto es solamente visible para administradores.</p>
            </div>
            <input type="submit" value="Subir" id="btn-enviar" disabled>
        </form>
    </div>
</dialog>