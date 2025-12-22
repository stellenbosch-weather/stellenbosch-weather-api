<?php
error_reporting(E_ALL);

require_once '../cors.php';
include "utils.php";

# Start timer
$time_start = microtime(true);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed. Please use POST.');
}

// Expect JSON in the post body
$json_data = json_decode(file_get_contents('php://input'), true);

// Check if JSON decode was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die('Invalid JSON data: ' . json_last_error_msg());
}

// Get the parameters from JSON POST data
$agree = isset($json_data['agree']) ? $json_data['agree'] : '';
$email = isset($json_data['email']) ? $json_data['email'] : '';

// Validate the agree parameter
if ($agree !== 'yes') {
    http_response_code(400);
    die('You must agree to the terms to download.');
}

// Validate the email parameter
if (empty($email) || !validEmail($email)) {
    http_response_code(400);
    die('A valid email address is required.');
}

if (isset($json_data['file'])) {
    $file = $json_data['file'];
    downloadFile($file);
}
else if (isset($json_data['start']) && isset($json_data['end'])) {
    try {
        $start = (new DateTime())->setTimestamp(intval($json_data['start']));
        $end = (new DateTime())->setTimestamp(intval($json_data['end']));
    } catch (Exception $e) {
        http_response_code(400);
        die('Invalid timestamp values provided.');
    }
    downloadHistory($start, $end);
}
else {
    http_response_code(400);
    die('Either a file or a time range (start and end) must be specified.');
}


$time_end = microtime(true);
$time = $time_end - $time_start;

// Log request and duration
logDownload($time, json_encode($json_data), $email);
