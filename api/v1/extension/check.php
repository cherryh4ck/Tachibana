<?php
    header("Content-Type: application/json");

    $pdo_mysql = false;
    $gd = false;
    $imagick = false;

    if (extension_loaded("pdo_mysql")){
        $pdo_mysql = true;
    }
    if (extension_loaded("gd")){
        $gd = true;
    }
    if (extension_loaded("imagick")){
        $imagick = true;
    }

    http_response_code(200);
    echo json_encode([
        "pdo_mysql" => $pdo_mysql,
        "gd" => $gd,
        "imagick" => $imagick
    ]);
?>