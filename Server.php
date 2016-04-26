<?php

/*
$array = [
    "method" => $_SERVER['REQUEST_METHOD'],
];

if (isset($_SERVER['HTTP_ACCEPT'])) {
    $array["accept"] = $_SERVER['HTTP_ACCEPT'];
}

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $array["authorization"] = $_SERVER['HTTP_AUTHORIZATION'];
}

if (isset($_SERVER['HTTP_CLIENTID'])) {
    $array["clientid"] = $_SERVER['HTTP_CLIENTID'];
}
*/
echo json_encode(["method" => $_SERVER['REQUEST_METHOD']]);//json_encode($array);