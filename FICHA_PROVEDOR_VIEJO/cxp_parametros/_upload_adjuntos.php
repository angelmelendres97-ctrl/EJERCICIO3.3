<?php
session_start();

$carpeta = "adjuntos_uafe/";

if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$response = ["success" => false, "files" => []];

if (!empty($_FILES['archivo']['name'])) {

    $tmp  = $_FILES['archivo']['tmp_name'];
    $name = $_FILES['archivo']['name'];

    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $nuevo = "adj_" . date("Ymd_His") . "_" . rand(100,999) . "." . $ext;

    $ruta = $carpeta . $nuevo;

    if (move_uploaded_file($tmp, $ruta)) {
        $response["success"] = true;
        $response["files"][] = $nuevo;
    }
}

echo json_encode($response);
