<?php

$files = [
    'services/captive-portal.blade.php' => ['url' => 'services_captiveportal.php', 'name' => 'Captive Portal'],
    'status/filter-reload.blade.php' => ['url' => 'status_filter_reload.php', 'name' => 'Filter Reload Status'],
    'status/monitoring.blade.php' => ['url' => 'status_monitoring.php', 'name' => 'Monitoring'],
    'status/ntp.blade.php' => ['url' => 'status_ntpd.php', 'name' => 'NTP Status'],
    'status/queues.blade.php' => ['url' => 'status_queues.php', 'name' => 'Queues Status'],
    'status/traffic-graph.blade.php' => ['url' => 'status_graph.php?if=wan', 'name' => 'Traffic Graph'],
    'status/upnp.blade.php' => ['url' => 'status_upnp.php', 'name' => 'UPnP & NAT-PMP Status'],
    'status/captive-portal.blade.php' => ['url' => 'status_captiveportal.php', 'name' => 'Captive Portal Status'],
    'status/dhcpv6-leases.blade.php' => ['url' => 'status_dhcpv6_leases.php', 'name' => 'DHCPv6 Leases'],
];

foreach ($files as $file => $data) {
    if (file_exists(__DIR__ . '/resources/views/' . $file)) {
        $content = file_get_contents(__DIR__ . '/resources/views/' . $file);

        // Pattern to match existing content inside the main content div
        // We look for the <x-app-layout> and inject/replace the content

        $newContent = <<<BLADE
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('{$data['name']}') }} - {{ \$firewall->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <x-api-not-supported :firewall="\$firewall" urlSuffix="{$data['url']}" featureName="{$data['name']}" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
BLADE;

        file_put_contents(__DIR__ . '/resources/views/' . $file, $newContent);
        echo "Updated $file\n";
    } else {
        echo "Skipped $file (not found)\n";
    }
}
