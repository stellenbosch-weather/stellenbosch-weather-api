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

    // Fetch min/max AirTC_Avg and their times for the current calendar day
    date_default_timezone_set('Africa/Johannesburg');
    $todayStart = date('Y-m-d 00:00:00');
    $todayEnd = date('Y-m-d 23:59:59');

    // Query for daily minimum (excluding NULLs)
    $minSql = "SELECT AirTC_Avg, Time FROM SB_TMin 
                   WHERE TimeStamp BETWEEN :start AND :end 
                   AND AirTC_Avg IS NOT NULL
                   ORDER BY AirTC_Avg ASC LIMIT 1";
    $minStmt = $conn->prepare($minSql);
    $minStmt->execute(['start' => $todayStart, 'end' => $todayEnd]);
    $minData = $minStmt->fetch(PDO::FETCH_ASSOC);

    // Query for daily maximum (excluding NULLs)
    $maxSql = "SELECT AirTC_Avg, Time FROM SB_TMin 
                   WHERE TimeStamp BETWEEN :start AND :end 
                   AND AirTC_Avg IS NOT NULL
                   ORDER BY AirTC_Avg DESC LIMIT 1";
    $maxStmt = $conn->prepare($maxSql);
    $maxStmt->execute(['start' => $todayStart, 'end' => $todayEnd]);
    $maxData = $maxStmt->fetch(PDO::FETCH_ASSOC);

    // Sum the minute totals since local midnight, preserving NULL when unavailable.
    $rainSql = "SELECT SUM(Rain_1_Tot) FROM SB_TMin
                WHERE TimeStamp >= :start AND TimeStamp <= :now";
    $rainStmt = $conn->prepare($rainSql);
    $rainStmt->execute(['start' => $todayStart, 'now' => date('Y-m-d H:i:s')]);
    $rainToday = $rainStmt->fetchColumn();

    // Merge stats into the result
    if ($result) {
        $result['min_temp'] = $minData ? $minData['AirTC_Avg'] : null;
        $result['min_temp_time'] = $minData ? $minData['Time'] : null;
        $result['max_temp'] = $maxData ? $maxData['AirTC_Avg'] : null;
        $result['max_temp_time'] = $maxData ? $maxData['Time'] : null;
        $result['rain_today'] = $rainToday !== null && $rainToday !== false ? (float) $rainToday : null;
    }

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
