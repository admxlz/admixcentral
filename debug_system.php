<?php

use App\Models\Firewall;
use App\Services\PfSenseApiService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$firewall = Firewall::first();
if (!$firewall) {
    echo "No firewall found.\n";
    exit(1);
}

$api = new PfSenseApiService($firewall);

$endpoints = [
    '/status/system',
    '/system/information',
    '/system/status',
];

foreach ($endpoints as $ep) {
    echo "Trying endpoint: $ep ... ";
    try {
        $response = $api->get($ep);
        echo "SUCCESS.\n";
        print_r($response);
    } catch (\Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
