<?php

require_once '../cors.php';
header('Content-Type: application/json');

$cache_file = '/tmp/met-no-stellenbosch-forecast.json';

$serve_cache = false;
$cached_response = null;

if (file_exists($cache_file)) {
    $cached_response = file_get_contents($cache_file);
    $data = json_decode($cached_response, true);

    if (isset($data['expires']) && time() < strtotime($data['expires'])) {
        $serve_cache = true;
    }
}

if ($serve_cache) {
    $data['data_source'] = 'cache';
    echo json_encode($data);
} else {
    // Cache is stale or doesn't exist, fetch new data
    $url = 'https://api.met.no/weatherapi/locationforecast/2.0/complete';
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: weather.sun.ac.za',
                'Accept: application/json'
            ]
        ]
    ];

    $context = stream_context_create($opts);

    // Stellenbosch coordinates
    $lat = -33.9321;
    $lon = 18.8602;

    $response = file_get_contents("$url?lat=$lat&lon=$lon", false, $context);

    if ($response !== false) {
        // Parse headers
        $headers = [];
        foreach ($http_response_header as $header) {
            if (strpos($header, ':') !== false) {
                list($key, $value) = explode(':', $header, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        // Decode JSON response
        $data = json_decode($response, true);

        // Add header info if available
        if (isset($headers['expires'])) {
            $data['expires'] = $headers['expires'];
        }
        if (isset($headers['last-modified'])) {
            $data['last_modified'] = $headers['last-modified'];
        }

        // Re-encode and cache the response
        $json_response = json_encode($data);
        file_put_contents($cache_file, $json_response);

        $data['data_source'] = 'fresh';
        echo json_encode($data);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch weather data']);
    }
}