<?php
    date_default_timezone_set('America/Argentina/Buenos_Aires');

    function e(?string $texto): string {
        $decodificado = html_entity_decode($texto ?? '', ENT_QUOTES, 'UTF-8');
        return htmlspecialchars($decodificado, ENT_QUOTES, 'UTF-8');
    }

    function formatear_descripcion(string $descripcion): string {
        $descripcion = str_replace(["<br>", "<br />"], "</p><p>", $descripcion);
        $descripcion = "<p>$descripcion</p>";
        $descripcion = preg_replace(
            '/<p>\s*(&gt;|>)(.*)<\/p>/',
            '<p id="post-comentarios-greentext">&gt;$2</p>',
            $descripcion
        );
        return $descripcion;
    }

    function rango_rol(string $rol): int {
        switch ($rol) {
            case "admin":
                return 2;
            case "mod":
                return 1;
            default:
                return 0;
        }
    }

    function esta_baneado(PDO $conn, int $id): ?array {
        $sql = $conn->prepare("SELECT * FROM bans WHERE id_usuario = ? ORDER BY id DESC LIMIT 1");
        $sql->execute([$id]);
        $fetch = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$fetch) {
            return null;
        }

        if ($fetch["expira"] !== null && strtotime($fetch["expira"]) <= time()) {
            $sql = $conn->prepare("DELETE FROM bans WHERE id = ?");
            $sql->execute([$fetch["id"]]);
            return null;
        }

        return [
            "motivo" => $fetch["motivo"],
            "expira" => $fetch["expira"]
        ];
    }

    function parsear_comentario_texto(string $comentario_texto, PDO $conn, int $id_post, int $comentario_id_actual, int $año_actual, bool $chequeo_estricto_imagen, bool $es_preview = false): string {
        $comentario_texto = str_replace(["<br>", "<br />"], "</p><p>", $comentario_texto);
        $comentario_texto = "<p>$comentario_texto</p>";

        $lineas = explode("</p><p>", $comentario_texto);
        $salida = "";

        foreach ($lineas as $linea) {
            $linea = preg_replace("/^<p>/", "", $linea);
            $linea = preg_replace("/<\/p>$/", "", $linea);
            $contenido = trim($linea);

            if (preg_match("/^(?:&gt;&gt;|>>)\s*(\d+)\s*$/", $contenido, $m)) {
                $id_salida = (int)$m[1];
                $sql = $conn->prepare("SELECT * FROM posts_comentarios WHERE id = ?");
                $sql->execute([$id_salida]);
                $newFetch = $sql->fetch(PDO::FETCH_ASSOC);

                if (($newFetch) && !($comentario_id_actual <= $id_salida)) {
                    if ($newFetch["id_post"] == $id_post) {
                        $es_op = ($newFetch["original_poster"] == 1);
                        $clase_respuesta = $es_op ? "respuesta respuesta-op" : "respuesta";
                        $texto_respuesta = "&gt;&gt;" . $id_salida . ($es_op ? " (OP)" : "");

                        if ($es_preview) {
                            $salida .= "<p class='$clase_respuesta' id='post-comentarios-respuesta'>$texto_respuesta</p>";
                        }
                        else {
                            if (!($newFetch["id_autor"] == 0)) {
                                $sql = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
                                $sql->execute([$newFetch["id_autor"]]);
                                $infoAutor = $sql->fetch(PDO::FETCH_ASSOC);
                            }

                            $dateTime = new DateTime($newFetch["fecha_creacion"]);
                            $año_comentario = (int)$dateTime->format('Y');
                            if ($año_comentario == $año_actual) {
                                $fecha_citada = $dateTime->format("d/m \a \l\a\s H:i");
                            }
                            else {
                                $fecha_citada = $dateTime->format("d/m/Y \a \l\a\s H:i");
                            }

                            if ($newFetch["id_autor"] == 0) {
                                $contenido_preview = "<div class='post-preview-username'><p><b>Anónimo</b><span id='post-preview-username-fecha2'>" . $fecha_citada . "</span></p></div>";
                            }
                            else {
                                $contenido_preview = "<div class='post-preview-username'><p><b>" . $infoAutor["nickname"] . "</b><span id='post-preview-username-nickname'>@" . $infoAutor["username"] . "</span><span id='post-preview-username-fecha'>" . $fecha_citada . "</span></p></div>";
                            }

                            $contenido_preview .= "<div class='post-preview-comentario'>" . parsear_comentario_texto($newFetch["comentario"], $conn, $id_post, $comentario_id_actual, $año_actual, $chequeo_estricto_imagen, true) . "</div>";

                            if ($newFetch["imagen_adjuntada"] == 1) {
                                $ruta_imagen_citada = "resources/posts/$id_post/{$newFetch['id']}.png";
                                $imagen_citada_existe = file_exists($ruta_imagen_citada);
                                if ($imagen_citada_existe || !$chequeo_estricto_imagen) {
                                    $contenido_preview .= "<img src='$ruta_imagen_citada' id='post-preview-comentario-imagen'>";
                                }
                            }

                            $salida .= "<p class='$clase_respuesta' id='post-comentarios-respuesta' data-id='$id_salida' data-content='" . htmlspecialchars($contenido_preview, ENT_QUOTES) . "'>$texto_respuesta</p>";
                        }
                    }
                    else {
                        $salida .= "<p id='post-comentarios-respuesta-invalida'>&gt;&gt;Respuesta inválida</p>";
                    }
                }
                else {
                    $salida .= "<p id='post-comentarios-respuesta-invalida'>&gt;&gt;Respuesta inválida</p>";
                }
            }
            elseif (preg_match("/^(?:&gt;|>)(.*)$/", $contenido, $m)) {
                $salida .= "<p id='post-comentarios-greentext'>$contenido</p>";
            }
            else {
                $salida .= "<p>$contenido</p>";
            }
        }

        return $salida;
    }

    function avatar_img(string $ruta, string $atributos_extra = ''): string {
        if (file_exists($ruta)) {
            $src = e($ruta) . '?v=' . filemtime($ruta);
        } else {
            $src = 'resources/avatar.png';
        }
        return "<img src='$src' alt='' $atributos_extra>";
    }

    function calcular_tiempo(string $fecha): string {
        $ahora = new DateTime();
        $fecha_obj = new DateTime($fecha);
        $diferencia = $ahora->diff($fecha_obj);

        if ($diferencia->y > 0) {
            return $diferencia->y . " año" . ($diferencia->y > 1 ? "s" : "");
        } elseif ($diferencia->m > 0) {
            return $diferencia->m . " mes" . ($diferencia->m > 1 ? "es" : "");
        } elseif ($diferencia->d > 0) {
            return $diferencia->d . " día" . ($diferencia->d > 1 ? "s" : "");
        } elseif ($diferencia->h > 0) {
            return $diferencia->h . " hora" . ($diferencia->h > 1 ? "s" : "");
        } elseif ($diferencia->i > 0) {
            return $diferencia->i . " minuto" . ($diferencia->i > 1 ? "s" : "");
        } else {
            $segundos = max(1, $diferencia->s);
            return $segundos . " segundo" . ($segundos > 1 ? "s" : "");
        }
    }

    define("POSTS_POR_PAGINA", 24);

    function construir_filtro_posts(PDO $conn): array {
        $ordenes_validos = ["ASC", "DESC", "ACTIVITY"];
        if (isset($_GET["orden"]) && in_array($_GET["orden"], $ordenes_validos, true)){
            $orden = strtoupper($_GET["orden"]);
        }
        else{
            $orden = "DESC";
        }

        $condiciones = [];
        $parametros = [];

        if (isset($_GET["categoria"]) && !($_GET["categoria"] == "all")){
            $categoria = $_GET["categoria"];
            $sql = $conn->prepare("SELECT * from categorias WHERE nombre = ?");
            $sql->execute([$categoria]);
            $fetch_categoria = $sql->fetch(PDO::FETCH_ASSOC);
            $id_categoria = $fetch_categoria ? $fetch_categoria["id"] : 1;
            $condiciones[] = "id_categoria = ?";
            $parametros[] = $id_categoria;
        }
        else{
            $categoria = "all";
        }

        if (isset($_GET["q"]) && !(empty($_GET["q"]))){
            $condiciones[] = "lower(titulo) LIKE ?";
            $parametros[] = "%" . $_GET["q"] . "%";
        }

        if (isset($_GET["tags"]) && !(empty($_GET["tags"]))){
            $tags_buscados = array_filter(array_map("trim", explode(",", strtolower($_GET["tags"]))));
            $tags_buscados = array_values(array_unique($tags_buscados));
        }
        else{
            $tags_buscados = [];
        }

        if (count($tags_buscados) > 0){
            $placeholders = implode(",", array_fill(0, count($tags_buscados), "?"));
            $condiciones[] = "id IN (SELECT id_post FROM posts_tags INNER JOIN tags ON posts_tags.id_tag = tags.id WHERE tags.nombre IN ($placeholders) GROUP BY id_post HAVING COUNT(DISTINCT tags.nombre) = ?)";
            $parametros = array_merge($parametros, $tags_buscados);
            $parametros[] = count($tags_buscados);
        }

        $where = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";

        return [
            "categoria" => $categoria,
            "orden" => $orden,
            "tags_buscados" => $tags_buscados,
            "where" => $where,
            "parametros" => $parametros,
        ];
    }

    function renderizar_post_card(array $post, PDO $conn): string {
        $post_titulo = "";
        $post_categoria = "";

        try {
            $sql = $conn->prepare("SELECT * FROM posts WHERE id = ?");
            $sql->execute([$post["id"]]);
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);
            if ($fetch){
                if ($post["baneado"] == 0){
                    $ruta_base = __DIR__ . "/../galeria/" . $post["id"];
                    if (file_exists($ruta_base . ".gif")){
                        $extension_miniatura = "gif";
                    }
                    else if (file_exists($ruta_base . ".jpg")){
                        $extension_miniatura = "jpg";
                    }
                    else{
                        return "";
                    }
                }

                $post_titulo = $fetch["titulo"];

                $sql = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
                $sql->execute([$fetch["id_categoria"]]);
                $fetch_categoria = $sql->fetch(PDO::FETCH_ASSOC);
                if ($fetch_categoria){
                    $post_categoria = $fetch_categoria["nombre"];
                }
            }
        }
        catch (PDOException $e){
            $html = "<div class='contenido-bloque contenido-bloque-phantom'>";
            $html .= "<a href='error.php?id=4'><img src='resources/notfound.jpg' alt=''></a>";
            $html .= "<p>(Eliminado)</p>";
            $html .= "</div>";
            return $html;
        }

        $html = "<div class='contenido-bloque'>";
        $html .= "<div class='contenido-bloque-categoria'>";
        if ($post["sticky"] == 0){
            $html .= "<span id='input-tag-rojo'>/$post_categoria/</span>";
        }
        else{
            $html .= "<span id='input-tag-amarillo'>Sticky</span>";
        }
        if ($post["archivado"] == 1){
            $html .= "<span id='post-card-archivado' title='Post archivado'><svg viewBox='0 0 24 24' width='14' height='14' fill='currentColor'><path d='M12 1a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-1V6a5 5 0 0 0-5-5zm-3 8V6a3 3 0 0 1 6 0v3z'/></svg></span>";
        }
        $html .= "</div>";
        if ($post["baneado"] == 1){
            $html .= "<a href='post.php?id=" . $post["id"] . "'><img src='resources/notfound.jpg' alt=''></a>";
        }
        else{
            $html .= "<a href='post.php?id=" . $post["id"] . "'><img src='galeria/" . $post["id"] . "." . $extension_miniatura . "' alt=''></a>";
        }
        $html .= "<p>";
        if ($post["sticky"] == 1){
            $html .= "<span id='post-titulo-fijado' title='Post fijado'><svg viewBox='0 0 24 24' width='16' height='16' fill='currentColor'><path d='M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z'/></svg></span>";
        }
        $html .= "<span class='contenido-bloque-titulo-texto'>$post_titulo</span>";
        $html .= "</p>";
        $html .= "</div>";

        return $html;
    }
?>