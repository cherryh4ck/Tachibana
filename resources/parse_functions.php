<?php
    function e(?string $texto): string {
        $decodificado = html_entity_decode($texto ?? '', ENT_QUOTES, 'UTF-8');
        return htmlspecialchars($decodificado, ENT_QUOTES, 'UTF-8');
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
        if (isset($_GET["orden"])){
            $orden = strtoupper($_GET["orden"]) == "ASC" ? "ASC" : "DESC";
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
        $ruta_imagen = __DIR__ . "/../galeria/" . $post["id"] . ".jpg";
        if (!file_exists($ruta_imagen)){
            return "";
        }

        $post_titulo = "";
        $post_categoria = "";

        try {
            $sql = $conn->prepare("SELECT * FROM posts WHERE id = ?");
            $sql->execute([$post["id"]]);
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);
            if ($fetch){
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
        $html .= "</div>";
        $html .= "<a href='post.php?id=" . $post["id"] . "'><img src='galeria/" . $post["id"] . ".jpg' alt=''></a>";
        $html .= "<p>";
        if ($post["sticky"] == 1){
            $html .= "<span id='post-titulo-fijado' title='Post fijado'><svg viewBox='0 0 24 24' width='16' height='16' fill='currentColor'><path d='M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z'/></svg></span>";
        }
        $html .= "$post_titulo</p>";
        $html .= "</div>";

        return $html;
    }
?>