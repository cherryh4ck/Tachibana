<?php
    // TODO: Sistema de busqueda ?
    // Sistema de indexado (Yo creo que estaría feo mostrar las imágenes borradas como inaccesibles)
    // Arreglar lo de que si el ID 1 no existe, colapsa todo el sistema xd
    session_start();
    require "php/db/config.php";
    require "resources/parse_functions.php";

    $instalado = is_dir("galeria");

    if ($instalado && $conn_test == 0){
        header("Location: error.php?id=9");
        exit();
    }

    if ($instalado){
        try{
            $filtro = construir_filtro_posts($conn);
            $categoria = $filtro["categoria"];
            $orden = $filtro["orden"];
            $tags_buscados = $filtro["tags_buscados"];
            $where = $filtro["where"];
            $parametros = $filtro["parametros"];

            $sql = $conn->prepare("SELECT * from posts $where ORDER BY sticky DESC, id $orden LIMIT " . POSTS_POR_PAGINA . " OFFSET 0");
            $sql->execute($parametros);
            $fetch_posts = $sql->fetchAll(PDO::FETCH_ASSOC);

            $sql = $conn->prepare("SELECT * FROM tags ORDER BY usos DESC LIMIT 5");
            $sql->execute();
            $fetch_tags = $sql->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            // mostrar error
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <script src="js/index/selectores.js" defer></script>
    <script src="js/index/query.js" defer></script>
    <script src="js/index/tags.js" defer></script>
    <script src="js/index/scroll.js" defer></script>

    <script src="js/subir_modal.js" defer></script>

    <link rel="stylesheet" href="styles/styles.css">
    <link rel="shortcut icon" href="favicon.ico" />
</head>
<body class="galerias">
    <?php include("resources/nav.php"); ?>
    <?php if ($instalado): ?>
    <div class="galeria">
        <div class="galeria-panel">
            <div class="galeria-herramientas">
                <h2>Búsqueda</h2>
                <div class="galeria-campo">
                    <h4>Título</h4>
                    <form action="index.php" method="GET" id="formulario-busqueda" class="galeria-campo-form">
                        <input type="hidden" name="categoria" value="<?php echo $categoria; ?>">
                        <input type="hidden" name="orden" value="<?php echo $orden; ?>">
                        <input type="hidden" name="tags" value="<?php echo htmlspecialchars(implode(",", $tags_buscados)); ?>" id="tags-valor-busqueda">
                        <input type="text" name="q" placeholder="Buscar por título..." id="input-busqueda" <?php if (isset($_GET["q"]) && !(empty($_GET["q"]))){ echo "value='" . htmlspecialchars($_GET["q"]) . "'";} ?>>
                        <input type="submit" value="Buscar" id="boton-busqueda">
                    </form>
                </div>
                <div class="galeria-campo">
                    <h4>Tags</h4>
                    <input type="text" placeholder="Escribí un tag y tocá Enter..." id="input-tag-busqueda">
                    <?php
                        if (count($tags_buscados) > 0){
                            echo "<p class='galeria-campo-hint'>Tags seleccionados</p>";
                            echo "<div class='galeria-tags-populares'>";
                            foreach ($tags_buscados as $tag_seleccionado){
                                echo "<span id='input-tag' class='tag-seleccionado' data-tag='" . htmlspecialchars($tag_seleccionado) . "'>" . htmlspecialchars($tag_seleccionado);
                                echo "<input type='button' id='remover-tag' class='tag-seleccionado-remover' value='x'>";
                                echo "</span>";
                            }
                            echo "</div>";
                        }
                    ?>
                    <?php
                        if ($fetch_tags){
                            echo "<p class='galeria-campo-hint'>Tags populares</p>";
                            echo "<div class='galeria-tags-populares'>";
                            if ($fetch_tags){
                                foreach ($fetch_tags as $tag){
                                    echo "<span id='input-tag' class='tag-popular' data-tag='" . htmlspecialchars($tag["nombre"]) . "'>" . $tag["nombre"] . "<b>" . $tag["usos"] . "</b></span>";
                                }
                            }
                            else{
                                echo "<p id='sin-resultados'>No hay resultados</p>";
                            }
                            echo "</div>";
                        }
                    ?>
                </div>
                <div class="galeria-campo">
                    <h4>Categoría</h4>
                    <div class="galeria-categoria-seleccionada">
                        <select name="categoria" id="categoria-input-index" size="1">
                                    <option value="all" <?php if ($categoria == "all"){ echo "selected";} ?>>Todos los posts</option>
                                    <option value="any" <?php if ($categoria == "any"){ echo "selected";} ?>>General - /any/</option>
                                    <option value="anime" <?php if ($categoria == "anime"){ echo "selected";} ?>>Anime - /anime/</option>
                                    <option value="manga" <?php if ($categoria == "manga"){ echo "selected";} ?>>Manga - /manga/</option>
                                    <option value="games" <?php if ($categoria == "games"){ echo "selected";} ?>>Videojuegos - /games/</option>
                                    <option value="pol" <?php if ($categoria == "pol"){ echo "selected";} ?>>Política - /pol/</option>
                                    <option value="tech" <?php if ($categoria == "tech"){ echo "selected";} ?>>Tecnología - /tech/</option>
                                    <option value="music" <?php if ($categoria == "music"){ echo "selected";} ?>>Música - /music/</option>
                                    <option value="movie" <?php if ($categoria == "movie"){ echo "selected";} ?>>Películas - /movie/</option>
                                    <option value="coding" <?php if ($categoria == "coding"){ echo "selected";} ?>>Programación - /coding/</option>
                        </select>
                        <?php
                            echo "<span id='input-tag-rojo-index'>/$categoria/</span>";
                        ?>
                    </div>
                </div>
                <div class="galeria-campo">
                    <h4>Ordenar por</h4>
                    <div class="galeria-categoria-seleccionada">
                        <select name="ordenar" id="categoria-input-categoria" size="1">
                                <option value="desc" <?php if ($orden == "DESC") { echo "selected";} ?>>Más reciente</option>
                                <option value="asc" <?php if ($orden == "ASC") { echo "selected";} ?>>Más antiguo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="galeria-panel2">
            <?php
                if (!$fetch_posts){
                    echo "<div class='galeria-imagenes-sin-posts'>";
                    if (isset($_GET["q"]) && !(empty($_GET["q"]))){
                        echo '<p id="sin-resultados-post">No se han encontrado resultados con el término "<b>' . $_GET["q"] . '</b>".</p>';
                    }
                    else{
                        echo '<p id="sin-resultados-post">No se han encontrado resultados.</p>';
                    }
                }
                else{
                    echo "<div class='galeria-imagenes'>";
                    foreach ($fetch_posts as $post){
                        echo renderizar_post_card($post, $conn);
                    }
                    echo "</div>";
                    if (count($fetch_posts) == POSTS_POR_PAGINA){
                        echo "<div id='galeria-cargar-mas' data-offset='" . POSTS_POR_PAGINA . "'>";
                        echo "<span class='galeria-cargar-mas-spinner'></span>";
                        echo "</div>";
                    }
                }
            ?>
            </div>
        </div>

        <?php include("resources/dialog-upload.php"); ?>
    </div>
    <?php else: ?>
    <?php include("resources/dialog-setup.php"); ?>
    <?php endif; ?>
</body>
</html>