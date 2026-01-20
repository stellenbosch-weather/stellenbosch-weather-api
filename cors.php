<?php

set_time_limit(10);

ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

$settings = parse_ini_file(__DIR__ . "/settings.conf", true);
if($settings['general']['debug']) {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

$allowedOrigins = array(
    'http://localhost:8082',
    'http://weather.sun.ac.za',
    'https://weather.sun.ac.za'
);

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
}

// Exit early if this is a preflight OPTIONS request
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}