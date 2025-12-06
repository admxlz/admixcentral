<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Firewall;
use App\Services\PfSenseApiService;
use Illuminate\Support\Facades\Http;

$firewall = Firewall::first();

if (!$firewall) {
    echo "No firewall found.\n";
    exit(1);
}

$api = new PfSenseApiService($firewall);

$endpoints = [
    '/system/webgui/settings',
    '/services/ssh',
    '/system/console',
    '/firewall/advanced_settings',
    '/system/notifications/email_settings',
    '/system/tunables',
];

echo "Probing endpoints for firewall: {$firewall->name} ({$firewall->url})\n";

foreach ($endpoints as $endpoint) {
    try {
        echo "Checking $endpoint ... ";
        $response = $api->get($endpoint);
        echo "OK\n";
    } catch (\Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
