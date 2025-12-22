<?php

class DatabaseConnector
{
    private $connection;

    function __construct()
    {
        $settings = parse_ini_file(__DIR__ . "/settings.conf", true);

        try {
            // Create a connection instance using DSN
            $dsn = "mysql:host=" . $settings['database']['host'] . ";port=" . $settings['database']['port'] . ";dbname=" . $settings['database']['database'];
            if ($settings['general']['debug']) {
                echo $dsn;
            }
            $timeout_seconds = 2;
            $conn = new PDO(
                $dsn,
                $settings['database']['username'],
                $settings['database']['password'],
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => $timeout_seconds
                )
            );

            $this->connection = $conn;
        } catch (PDOException $e) {
            // Handle connection errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => "MySQL database connection failed: " . $e->getMessage()]);
            die();
        }
    }

    function getConnection()
    {
        return $this->connection;
    }

    function closeConnection()
    {
        $this->connection = null;
    }

}
