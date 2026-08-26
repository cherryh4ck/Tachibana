<?php
    header("Content-Type: application/json");

    if (extension_loaded("imagick")){
        http_response_code(200);
        echo json_encode(["ok" => true]);
        exit();
    }
    else{
        http_response_code(500);
        echo json_encode(["ok" => false]);
        exit();
    }
?>