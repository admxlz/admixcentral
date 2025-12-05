<?php

use App\Models\Firewall;
use App\Services\PfSenseApiService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$firewalls = Firewall::all();

foreach ($firewalls as $firewall) {
    echo "Processing firewall: {$firewall->name} (ID: {$firewall->id}) ... ";
    try {
        $api = new PfSenseApiService($firewall);
        $response = $api->get('/status/system');
        $netgateId = $response['data']['netgate_id'] ?? null;

        if ($netgateId) {
            $firewall->netgate_id = $netgateId;
            $firewall->save();
            echo "UPDATED with ID: $netgateId\n";
        } else {
            echo "FAILED: No Netgate ID found in response.\n";
            print_r($response);
        }
    } catch (\Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
