<?php
    $configuracion = require __DIR__ . "/config-data.php";

    // configuracion
    $settings_id = $configuracion["general"]["settings_id"];
    $motd_activado = $configuracion["general"]["motd_activado"];

    // posts
    $chequeo_estricto_imagen = $configuracion["posts"]["chequeo_estricto_imagen"];

    // bd
    $host = $configuracion["network"]["host"];
    $puerto = $configuracion["network"]["puerto"];
    $user = $configuracion["network"]["username"];
    $pass = $configuracion["network"]["password"];
    $db = $configuracion["network"]["database"];

    // seguridad
    $mantenimiento = $configuracion["seguridad"]["mantenimiento"];
    $debug = $configuracion["seguridad"]["debug"];

    if ($debug == 0){
        error_reporting(E_ERROR | E_PARSE);
    }

    $conn_test = 0;
    try{
        $conn = new PDO("mysql:host=$host:$puerto;dbname=$db", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn_test = 1;
    }
    catch (PDOException $e){
        $conn_test = 0;
    }

    if ($conn_test == 1){
        require "cookie_auth.php";
        require "ult_act.php";
        session_start();
    }
?>
