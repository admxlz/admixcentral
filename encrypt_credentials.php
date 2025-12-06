<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting credential encryption...\n";

$firewalls = DB::table('firewalls')->get();

foreach ($firewalls as $firewall) {
    echo "Processing Firewall ID: {$firewall->id}\n";

    $updates = [];

    // Encrypt api_key if present and not already encrypted (naive check, but safe since we know state)
    // Actually, Crypt::encryptString generates a long string. Plain text keys are usually shorter.
    // But relying on length isn't great. We assume ALL are plain text right now.

    if (!empty($firewall->api_key)) {
        try {
            // Check if already encrypted (try to decrypt)
            Crypt::decryptString($firewall->api_key);
            echo " - API Key execution skipped (already encrypted?)\n";
        } catch (\Exception $e) {
            // Decrypt failed, implies it is plain text. Encrypt it.
            $updates['api_key'] = Crypt::encryptString($firewall->api_key);
            echo " - Encrypting API Key\n";
        }
    }

    if (!empty($firewall->api_secret)) {
        try {
            Crypt::decryptString($firewall->api_secret);
            echo " - API Secret skipped (already encrypted?)\n";
        } catch (\Exception $e) {
            $updates['api_secret'] = Crypt::encryptString($firewall->api_secret);
            echo " - Encrypting API Secret\n";
        }
    }

    if (!empty($updates)) {
        DB::table('firewalls')->where('id', $firewall->id)->update($updates);
        echo " - Updated.\n";
    } else {
        echo " - No changes needed.\n";
    }
}

echo "Encryption complete.\n";
