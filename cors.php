<?php

$allowedOrigins = array(
    'http://localhost:8082',
    'http://weather.sun.ac.za',
    'https://weather.sun.ac.za'
);

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
}