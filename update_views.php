<?php

$files = [
    'diagnostics/backup-restore.blade.php' => ['url' => 'diag_backup.php', 'name' => 'Backup & Restore'],
    'diagnostics/edit-file.blade.php' => ['url' => 'diag_edit.php', 'name' => 'Edit File'],
    'diagnostics/limiter-info.blade.php' => ['url' => 'diag_limiter_info.php', 'name' => 'Limiter Info'],
    'diagnostics/ndp-table.blade.php' => ['url' => 'diag_ndp.php', 'name' => 'NDP Table'],
    'diagnostics/packet-capture.blade.php' => ['url' => 'diag_packet_capture.php', 'name' => 'Packet Capture'],
    'diagnostics/pf-info.blade.php' => ['url' => 'diag_pf_info.php', 'name' => 'pfInfo'],
    'diagnostics/pf-top.blade.php' => ['url' => 'diag_system_pftop.php', 'name' => 'pfTop'],
    'diagnostics/routes.blade.php' => ['url' => 'diag_routes.php', 'name' => 'Routes'],
    'diagnostics/smart-status.blade.php' => ['url' => 'diag_smart.php', 'name' => 'SMART Status'],
    'diagnostics/sockets.blade.php' => ['url' => 'diag_sockets.php', 'name' => 'Sockets'],
    'diagnostics/states-summary.blade.php' => ['url' => 'diag_states_summary.php', 'name' => 'States Summary'],
    'diagnostics/system-activity.blade.php' => ['url' => 'diag_system_activity.php', 'name' => 'System Activity'],
    'diagnostics/test-port.blade.php' => ['url' => 'diag_testport.php', 'name' => 'Test Port'],
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
