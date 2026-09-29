<?php

function loadSettings()
{
    $home = getenv('HOME');
    $candidates = [
        __DIR__ . '/settings.conf',
        dirname(dirname(__DIR__)) . '/settings.conf',
    ];

    if ($home !== false && $home !== '') {
        $candidates[] = rtrim($home, DIRECTORY_SEPARATOR) . '/settings.conf';
    }

    foreach (array_unique($candidates) as $path) {
        if (!is_readable($path)) {
            continue;
        }

        $settings = parse_ini_file($path, true);
        if ($settings === false) {
            throw new RuntimeException('Unable to parse settings file: ' . $path);
        }

        return $settings;
    }

    throw new RuntimeException(
        'No settings.conf found. Checked: ' . implode(', ', array_unique($candidates))
    );
}
