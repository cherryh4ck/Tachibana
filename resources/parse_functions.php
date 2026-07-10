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
?>