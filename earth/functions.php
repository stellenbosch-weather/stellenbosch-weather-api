<?php

function exit_json_message($message)
{
    http_response_code(400);
    echo $message;
    exit;
}

function fetch_clouds($useCache, $cloudUrl, $cloudFile, $cloudTtlSeconds)
{
    if ($useCache && file_exists($cloudFile)) {
        $cmtime = @filemtime($cloudFile);
        if ($cmtime !== false && (time() - $cmtime) < $cloudTtlSeconds && filesize($cloudFile) > 0) {
            return true;
        }
    }

    $opts = array(
        'http' => array(
            'method' => 'GET',
            'timeout' => 20,
            'header' => "User-Agent: weather-stellenbosch-api/1.0\r\n",
        ),
        'ssl' => array(
            'verify_peer' => true,
            'verify_peer_name' => true,
        ),
    );
    $context = stream_context_create($opts);
    $cloudData = @file_get_contents($cloudUrl, false, $context);

    if ($cloudData !== false && strlen($cloudData) > 0) {
        @file_put_contents($cloudFile, $cloudData);
        if (file_exists($cloudFile) && filesize($cloudFile) > 0) {
            return true;
        }
    }

    return false;
}

function apply_cloud_overlay($earthDayTexturePath, $earthNightTexturePath, $cloudFile)
{
    // Manually apply cloud overlay to earth day/night textures, as xplanet messes it up
    $earthDayImage = imagecreatefromjpeg($earthDayTexturePath);
    if (!$earthDayImage) {
        http_response_code(500);
        echo "Failed to load earth day texture";
        exit;
    }

    $earthNightImage = imagecreatefromjpeg($earthNightTexturePath);
    if (!$earthNightImage) {
        http_response_code(500);
        echo "Failed to load earth night texture";
        exit;
    }

    $cloudImage = imagecreatefrompng($cloudFile);
    if (!$cloudImage) {
        http_response_code(500);
        echo "Failed to load cloud texture";
        exit;
    }

// Day
    imagealphablending($earthDayImage, true);
    imagesavealpha($earthDayImage, true);
    imagecopy($earthDayImage, $cloudImage, 0, 0, 0, 0, imagesx($cloudImage), imagesy($cloudImage));
// Night
    imagealphablending($earthNightImage, true);
    imagesavealpha($earthNightImage, true);
    imagecopy($earthNightImage, $cloudImage, 0, 0, 0, 0, imagesx($cloudImage), imagesy($cloudImage));


// Save processed images to temp files
    $tmpDayFile = tempnam(sys_get_temp_dir(), 'xpearth_day_');
    $tmpNightFile = tempnam(sys_get_temp_dir(), 'xpearth_night_');
    if ($tmpDayFile === false || $tmpNightFile === false) {
        http_response_code(500);
        echo "Failed to create temp image files";
        exit;
    }

// Save earth with cloud images to tmp
    imagepng($earthDayImage, $tmpDayFile);
    imagepng($earthNightImage, $tmpNightFile);
    imagedestroy($earthDayImage);
    imagedestroy($earthNightImage);
    imagedestroy($cloudImage);

    return array(
        'day' => $tmpDayFile,
        'night' => $tmpNightFile
    );
}

function crop_image($tmpOutPng) {
// Crop excess black borders
    $borderSides = 40;
    $borderTopBtm = 20;

    $img = imagecreatefrompng($tmpOutPng);
    if (!$img) {
        exit_json_message("Failed to load rendered image for cropping");
    }

// Find content bounds
    $width = imagesx($img);
    $height = imagesy($img);
    $bounds = array('left' => $width, 'top' => $height, 'right' => 0, 'bottom' => 0);

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($img, $x, $y);
            if ($rgb > 0) {
                $bounds['left'] = min($bounds['left'], $x);
                $bounds['top'] = min($bounds['top'], $y);
                $bounds['right'] = max($bounds['right'], $x);
                $bounds['bottom'] = max($bounds['bottom'], $y);
            }
        }
    }

// Add border
    $bounds['left'] = max(0, $bounds['left'] - $borderSides);
    $bounds['top'] = max(0, $bounds['top'] - $borderTopBtm);
    $bounds['right'] = min($width - 1, $bounds['right'] + $borderSides);
    $bounds['bottom'] = min($height - 1, $bounds['bottom'] + $borderTopBtm);

// Crop
    $newWidth = $bounds['right'] - $bounds['left'] + 1;
    $newHeight = $bounds['bottom'] - $bounds['top'] + 1;
    $cropped = imagecreatetruecolor($newWidth, $newHeight);
    imagecopy($cropped, $img, 0, 0, $bounds['left'], $bounds['top'], $newWidth, $newHeight);
    imagedestroy($img);

// Save cropped image
    imagepng($cropped, $tmpOutPng);
    imagedestroy($cropped);
}