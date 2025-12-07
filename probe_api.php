<?php

echo "Starting probe script...\n";

require __DIR__ . '/vendor/autoload.php';

echo "Vendor autoloaded.\n";

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Laravel bootstrapped.\n";

$firewall = \App\Models\Firewall::first();
if (!$firewall) {
    echo "No firewall found.\n";
    exit(1);
}

echo "Firewall found: {$firewall->name}\n";

$api = new \App\Services\PfSenseApiService($firewall);

echo "API Service initialized.\n";

$endpoints = [
    // NTP
    '/services/ntp',
    '/api/v1/services/ntp',
    '/api/v2/services/ntp',
    '/system/ntp',

    // SNMP
    '/services/snmp',
    '/api/v1/services/snmp',
    '/api/v2/services/snmp',
    '/services/snmpd',

    // Captive Portal
    '/services/captiveportal',
    '/services/captive_portal',
    '/api/v1/services/captiveportal',
    '/api/v1/services/captiveportal/zone', // Some APIs use this

    // UPnP
    '/services/upnp',
    '/services/miniupnpd',
    '/api/v1/services/upnp',
];

echo "Probing API endpoints for firewall: {$firewall->name} (ID: {$firewall->id})\n";

foreach ($endpoints as $endpoint) {
    echo "Probing: $endpoint ... ";
    try {
        $response = $api->get($endpoint);
        echo "FOUND! (Success)\n";
        // print_r($response);
    } catch (\Exception $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, '404') || str_contains($msg, 'Not Found')) {
            echo "Not Found (404)\n";
        } elseif (str_contains($msg, '401') || str_contains($msg, 'Unauthorized')) {
            echo "Unauthorized (401)\n";
        } else {
            // Print first 50 chars of error to avoid massive HTML dumps
            echo "Error: " . substr($msg, 0, 100) . "...\n";
        }
    }
}
echo "Probe complete.\n";
