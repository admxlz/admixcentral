<?php
$json = file_get_contents('/home/baga/Code/admixcentral/openapi.json');
if ($json === false) {
    die("Failed to read openapi.json\n");
}
echo "Read " . strlen($json) . " bytes.\n";
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON Decode Error: " . json_last_error_msg() . "\n");
}
echo "Keys: " . implode(', ', array_keys($data)) . "\n";
if (isset($data['paths'])) {
    echo "First 5 paths:\n";
    $count = 0;
    foreach ($data['paths'] as $path => $methods) {
        echo $path . "\n";
        $count++;
        if ($count >= 5)
            break;
    }
} else {
    echo "No 'paths' key found.\n";
}
