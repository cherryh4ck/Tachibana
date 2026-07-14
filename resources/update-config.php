<?php
    function actualizar_config(array $cambios){
        $config = require __DIR__ . "/../php/db/config-data.php";

        foreach ($cambios as $seccion => $claves){
            foreach ($claves as $clave => $valor){
                $config[$seccion][$clave] = $valor;
            }
        }

        file_put_contents(
            __DIR__ . "/../php/db/config-data.php",
            "<?php\nreturn " . var_export($config, true) . ";\n"
        );
    }
?>