<?php

require_once '../database-connector.php';
require_once '../cors.php';
header('Content-Type: application/json');

try {
    // Get and sanitize start/end parameters - unix timestamp integers
    $start = filter_input(INPUT_GET, 'start', FILTER_VALIDATE_INT) ?: strtotime('-24 hours');
    // Check for end get parameter, default to the current time if not present
    $end = filter_input(INPUT_GET, 'end', FILTER_VALIDATE_INT) ?: time();

    // convert the unix timestamps in $start and $end to ISO 8601 format
    date_default_timezone_set('Africa/Johannesburg');
    $start = date('c', $start);
    $end = date('c', $end);

    $db = new DatabaseConnector();
    $conn = $db->getConnection();

    $sql = "SELECT * FROM SB_TMin WHERE TimeStamp BETWEEN :start AND :end ORDER BY TimeStamp";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':start', $start);
    $stmt->bindParam(':end', $end);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
