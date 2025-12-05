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
    '/services/freeradius/settings',
    '/services/freeradius/setting',
    '/services/freeradius/config',
    '/services/freeradius',
    '/services/freeradius/user',   // Expected to fail or require ID
];

foreach ($endpoints as $ep) {
    echo "Trying endpoint: $ep ... ";
    try {
        $response = $api->get($ep);
        echo "SUCCESS. Keys: " . implode(', ', array_keys($response['data'] ?? $response)) . "\n";
    } catch (\Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
