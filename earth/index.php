<?php
/**
 * sun/earth-mollweide.php
 *
 * Generates a day/night (terminator) view of Earth in Mollweide projection using xplanet.
 */

require_once '../cors.php';

include_once __DIR__ . '/functions.php';

$useCache = true;
$cacheTtlSeconds = 60 * 60; // 1 hour
$cacheFile = sys_get_temp_dir() . '/earth-mollweide-daynight.png';

if ($useCache && file_exists($cacheFile)) {
    $mtime = @filemtime($cacheFile);
    if ($mtime !== false && (time() - $mtime) < $cacheTtlSeconds) {
        header('Content-Type: image/png');
        readfile($cacheFile);
        exit;
    }
}

// Fetch live cloud overlay from https://github.com/matteason/live-cloud-maps
$cloudUrl = 'https://clouds.matteason.co.uk/images/2048x1024/clouds-alpha.png';
$cloudFile = sys_get_temp_dir() . '/clouds_alpha_2048x1024.png';
if (!fetch_clouds($useCache, $cloudUrl, $cloudFile, $cacheTtlSeconds)) {
    exit_json_message("Failed to fetch cloud map");
}

// Load earth day/night textures
$earthDayTexturePath = __DIR__ . '/2k_earth_daymap.jpg';
if (!file_exists($earthDayTexturePath)) {
    exit_json_message("Missing earth day texture");
}
$earthNightTexturePath = __DIR__ . '/2k_earth_nightmap.jpg';
if (!file_exists($earthNightTexturePath)) {
    exit_json_message("Missing earth night texture");
}

$earthWithOverlay = apply_cloud_overlay($earthDayTexturePath, $earthNightTexturePath, $cloudFile);

// File to render result to
$tmpOut = tempnam(sys_get_temp_dir(), 'xpearth_');
if ($tmpOut === false) {
    exit_json_message("Failed to create temp file for output image");
}
@unlink($tmpOut);
$tmpOutPng = $tmpOut . '.png';

// Create a temp config file
$tmpCfg = tempnam(sys_get_temp_dir(), 'xplanet_cfg_');
if ($tmpCfg === false) {
    exit_json_message("Failed to create temp file for xplanet config");
}

// xplanet config file
$cfg =
    "[default]\n" .
    "label=false\n" .
    "background=black\n" .
    "\n" .
    "[earth]\n" .
    "map=" . $earthWithOverlay['day'] . "\n" .
    "night_map=" . $earthWithOverlay['night'] . "\n";

if (@file_put_contents($tmpCfg, $cfg) === false) {
    exit_json_message("Failed to write temp config file");
}

// Anything other than a square makes xplanet stretch the image
$geometry = '1600x1600';

$cmd =
    '../xplanet/xplanet' .
    ' -num_times 1' .
    ' -config ' . escapeshellarg($tmpCfg) .
    ' -body earth' .
    ' -projection mollweide' .
    ' -geometry ' . escapeshellarg($geometry) .
    ' -quality 95' .
    ' -background black' .
    ' -output ' . escapeshellarg($tmpOutPng) .
    ' 2>&1';

$out = array();
$exitCode = 0;
@exec($cmd, $out, $exitCode);

// delete temp files
@unlink($tmpCfg);
@unlink($earthWithOverlay['day']);
@unlink($earthWithOverlay['night']);

if ($exitCode !== 0 || !file_exists($tmpOutPng) || filesize($tmpOutPng) === 0) {
    @unlink($tmpOutPng);
    exit_json_message("xplanet render failed.\n\nCommand:\n" . $cmd . "\n\nExit code:\n" . $exitCode . "\n\nOutput:\n" . implode("\n", $out));
}

crop_image($tmpOutPng);

// Atomically update cache
@rename($tmpOutPng, $cacheFile);

header('Content-Type: image/png');
readfile($cacheFile);
