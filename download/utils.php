<?php

require_once __DIR__ . '/../database-connector.php';

/**
 * @param string $time
 * @param string $postData
 * @param string $email
 */
function logDownload($time, $postData, $email)
{
    $db = new DatabaseConnector();
    $conn = $db->getConnection();

    $ip = $_SERVER['REMOTE_ADDR'];

    $query = "INSERT INTO `weatheDataDownload` (`DateTime`, `IP`, `Post`, `Time`, `Email`) 
              VALUES (NOW(), :ip, :postData, :time, :email)";

    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':ip' => $ip,
        ':postData' => $postData,
        ':time' => $time,
        ':email' => $email
    ]);
}

//http://www.linuxjournal.com/article/9585
/**
 * Validate an email address.
 * Provide email address (raw input)
 * Returns true if the email address has the email
 * address format and the domain exists.
 */
function validEmail($email)
{
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
        $isValid = false;
    } else {
        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $isValid = false;
        } else if ($domainLen < 1 || $domainLen > 255) {
            // domain part length exceeded
            $isValid = false;
        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            // local part starts or ends with '.'
            $isValid = false;
        } else if (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $isValid = false;
        } else if (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $isValid = false;
        } else if
        (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                str_replace("\\\\", "", $local))) {
            // character not valid in local part unless
            // local part is quoted
            if (!preg_match('/^"(\\\\"|[^"])+"$/',
                str_replace("\\\\", "", $local))) {
                $isValid = false;
            }
        }
        if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
            // domain not found in DNS
            $isValid = false;
        }
    }
    return $isValid;
}

function downloadFile($file)
{
    // Define allowed files with their paths
    $allowed_files = [
        'dc' => __DIR__ . '/Weather_decingel.zip',
        'mr' => __DIR__ . '/Weather_mmroof.zip',
        'ee' => __DIR__ . '/WeatherEE.zip',
        'mm' => __DIR__ . '/WeatherMM.zip',
    ];

// Validate the file parameter
    if (empty($file) || !array_key_exists($file, $allowed_files)) {
        http_response_code(400);
        die('Invalid file parameter');
    }

// Get the file path
    $file_path = $allowed_files[$file];

// Check if file exists
    if (!file_exists($file_path)) {
        http_response_code(404);
        die('File not found');
    }

// Set headers for file download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));

// Output the file
    readfile($file_path);
}
