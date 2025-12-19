<?php

require_once '../database-connector.php';
require_once '../cors.php';
header('Content-Type: application/json');

try {
    $db = new DatabaseConnector();
    $conn = $db->getConnection();

    $sql = "SELECT * FROM SB_TMin ORDER BY TimeStamp DESC LIMIT 1";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}