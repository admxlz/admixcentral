<?php
$files = ['openapi.json', 'openapi_new.json'];

foreach ($files as $file) {
    echo "Checking $file...\n";
    if (!file_exists($file)) {
        echo "$file not found.\n";
        continue;
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Error in $file: " . json_last_error_msg() . "\n";
        continue;
    }

    if (isset($data['paths'])) {
        echo "Paths in $file:\n";
        foreach (array_keys($data['paths']) as $path) {
            if (strpos($path, 'status') !== false || strpos($path, 'ipsec') !== false || strpos($path, 'openvpn') !== false) {
                echo $path . "\n";
            }
        }
    } else {
        echo "No paths found in $file.\n";
    }
    echo "--------------------------------\n";
}
