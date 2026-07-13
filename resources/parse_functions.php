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
?>