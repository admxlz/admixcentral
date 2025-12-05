<?php
use App\Models\Firewall;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$firewalls = Firewall::all();
echo "Found " . $firewalls->count() . " firewalls.\n";
foreach ($firewalls as $fw) {
    echo "ID: " . $fw->id . " Name: " . $fw->name . " Netgate ID: " . ($fw->netgate_id ?? 'NULL') . "\n";
}
