<?php


$settings = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/settings.conf", true);

if($settings['general']['debug']) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

$allowedOrigins = array(
    'http://localhost:8082',
    'http://weather.sun.ac.za',
    'https://weather.sun.ac.za'
);

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
}