<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Mock Laravel's Http facade for standalone script or just use curl
function fetchApi($path)
{
    $url = "https://172.30.1.129:444/api/v1" . $path; // v1 or v2? usually v2 based on previous context
    $url_v2 = "https://172.30.1.129:444/api/v2" . $path;

    echo "Probing $url_v2 ...\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_v2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERPWD, "admin:pfsense");

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Status: $httpCode\n";
    if ($httpCode == 200) {
        echo "Response: " . substr($result, 0, 500) . "...\n";
    } else {
        echo "Error: " . substr($result, 0, 200) . "\n";
    }
    echo "\n";
}

// Probe potential endpoints
fetchApi('/services/dhcpv6-relay');
fetchApi('/services/dhcpv6_relay');
fetchApi('/services/dhcp6-relay');
fetchApi('/services/dhcp6_relay'); // Sometimes naming varies
fetchApi('/services/dhcp-relay6');
fetchApi('/services/dhcp_relay6');

// Check regular relay just in case it handles v6
fetchApi('/services/dhcp-relay');

