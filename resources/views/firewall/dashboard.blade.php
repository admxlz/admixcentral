<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $firewall->name }} - Dashboard
            </h2>
            <a href="{{ route('firewalls.edit', $firewall) }}"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Edit Settings
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(isset($apiError) && $apiError)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Warning</p>
                    <p>Some data could not be retrieved: {{ $apiError }}</p>
                </div>
            @endif

            {{-- System Status --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">System Information</h3>
                        @if(isset($systemStatus['connected']) && $systemStatus['connected'])
                            <span
                                class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Online</span>
                        @else
                            <span
                                class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Offline</span>
                        @endif
                    </div>

                    @if(isset($systemStatus['connected']) && $systemStatus['connected'])
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Left Column: System Details --}}
                            <div>
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <tbody>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                Name</th>
                                            <td class="py-2">{{ $systemStatus['data']['hostname'] ?? $firewall->name }}</td>
                                        </tr>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                Netgate Device ID</th>
                                            <td class="py-2 font-mono">{{ $systemStatus['data']['netgate_id'] ?? 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                Version</th>
                                            <td class="py-2">
                                                {{ $systemStatus['data']['version'] ?? '2.8.1-RELEASE' }}
                                                <div class="text-xs text-gray-400">built on
                                                    {{ $systemStatus['data']['built_on'] ?? 'Unknown' }}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                REST API Version</th>
                                            <td class="py-2">{{ $systemStatus['api_version'] ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                Platform</th>
                                            <td class="py-2">{{ $systemStatus['data']['platform'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                BIOS</th>
                                            <td class="py-2">
                                                Vendor: {{ $systemStatus['data']['bios_vendor'] ?? 'N/A' }} <br>
                                                Version: {{ $systemStatus['data']['bios_version'] ?? 'N/A' }} <br>
                                                Date: {{ $systemStatus['data']['bios_date'] ?? 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">CPU
                                                System</th>
                                            <td class="py-2">
                                                <div class="flex flex-col">
                                                    <span>{{ $systemStatus['data']['cpu_model'] ?? 'N/A' }}</span>
                                                    <span
                                                        class="text-xs text-gray-500">{{ $systemStatus['data']['cpu_count'] ?? '1' }}
                                                        CPUs</span>
                                                </div>
                                                <div class="mt-1 flex items-center space-x-2 text-xs">
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                        Load:
                                                        {{ implode(', ', $systemStatus['data']['cpu_load_avg'] ?? ['-', '-', '-']) }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                Uptime</th>
                                            <td class="py-2">{{ $systemStatus['data']['uptime'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                Security</th>
                                            <td class="py-2 space-y-1">
                                                <div class="flex items-center">
                                                    <span class="text-xs font-semibold w-24">Kernel PTI:</span>
                                                    @if(isset($systemStatus['data']['kernel_pti']) && $systemStatus['data']['kernel_pti'] == '1')
                                                        <span
                                                            class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Enabled</span>
                                                    @else
                                                        <span
                                                            class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Disabled</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-xs font-semibold w-24">MDS Mitigation:</span>
                                                    <span
                                                        class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                        {{ ucfirst($systemStatus['data']['mds_mitigation'] ?? 'Inactive') }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Right Column: Usage Micrographs --}}
                            <div>
                                {{-- CPU Usage --}}
                                <div class="mb-4">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">CPU Usage</span>
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $systemStatus['data']['cpu_usage'] ?? '0' }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-blue-600 h-2.5 rounded-full"
                                            style="width: {{ $systemStatus['data']['cpu_usage'] ?? '0' }}%"></div>
                                    </div>
                                </div>

                                {{-- Memory Usage --}}
                                <div class="mb-4">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Memory
                                            Usage</span>
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $systemStatus['data']['mem_usage'] ?? '0' }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-purple-600 h-2.5 rounded-full"
                                            style="width: {{ $systemStatus['data']['mem_usage'] ?? '0' }}%"></div>
                                    </div>
                                </div>

                                {{-- Swap Usage --}}
                                <div class="mb-4">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Swap Usage</span>
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $systemStatus['data']['swap_usage'] ?? '0' }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-red-600 h-2.5 rounded-full"
                                            style="width: {{ $systemStatus['data']['swap_usage'] ?? '0' }}%"></div>
                                    </div>
                                </div>

                                {{-- Disk Usage --}}
                                <div class="mb-4">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Disk Usage
                                            (/)</span>
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $systemStatus['data']['disk_usage'] ?? '0' }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-yellow-600 h-2.5 rounded-full"
                                            style="width: {{ $systemStatus['data']['disk_usage'] ?? '0' }}%"></div>
                                    </div>
                                </div>

                                {{-- MBUF Usage --}}
                                <div class="mb-4">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">MBUF Usage</span>
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $systemStatus['data']['mbuf_usage'] ?? '0' }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-indigo-600 h-2.5 rounded-full"
                                            style="width: {{ $systemStatus['data']['mbuf_usage'] ?? '0' }}%"></div>
                                    </div>
                                </div>

                                {{-- Temperature (if available) --}}
                                @if(!empty($systemStatus['data']['temp_c']))
                                    <div class="mb-4">
                                        <div class="flex justify-between mb-1">
                                            <span
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">Temperature</span>
                                            <span
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $systemStatus['data']['temp_c'] }}°C</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                            {{-- Assuming 100C is max for bar --}}
                                            <div class="bg-orange-600 h-2.5 rounded-full"
                                                style="width: {{ min($systemStatus['data']['temp_c'], 100) }}%"></div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    @else
                        <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                            <p class="text-red-600 dark:text-red-400 font-semibold">Unable to connect to firewall.</p>
                            <p class="text-sm text-red-500">{{ $apiError ?? 'Check connectivity and credentials.' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Nav --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Management</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('firewall.interfaces.index', $firewall) }}"
                            class="bg-blue-500 hover:bg-blue-700 text-white p-4 rounded-lg text-center transition">
                            <p class="font-bold">Interfaces</p>
                            <p class="text-sm">{{ isset($interfaces['data']) ? count($interfaces['data']) : 0 }}</p>
                        </a>
                        <a href="{{ route('firewall.rules.index', $firewall) }}"
                            class="bg-purple-500 hover:bg-purple-700 text-white p-4 rounded-lg text-center transition">
                            <p class="font-bold">Firewall Rules</p>
                            <p class="text-sm">{{ isset($firewallRules['data']) ? count($firewallRules['data']) : 0 }}
                            </p>
                        </a>
                        <a href="#"
                            class="bg-green-500 hover:bg-green-700 text-white p-4 rounded-lg text-center transition">
                            <p class="font-bold">Services</p>
                            <p class="text-sm">Manage</p>
                        </a>
                        <a href="#"
                            class="bg-orange-500 hover:bg-orange-700 text-white p-4 rounded-lg text-center transition">
                            <p class="font-bold">VPN</p>
                            <p class="text-sm">Configure</p>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Interfaces Summary --}}
            @if(isset($interfaces['data']) && count($interfaces['data']) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Interfaces</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Interface</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Description</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            IP Address</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($interfaces['data'] as $interface)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap font-mono">
                                                {{ $interface['id'] ?? $interface['if'] ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $interface['descr'] ?? $interface['description'] ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if(isset($interface['enable']) && $interface['enable'])
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                        Enabled
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                        Disabled
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                                                {{ $interface['ipaddr'] ?? $interface['ipaddrv4'] ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Gateways Summary --}}
            @if(isset($gateways) && count($gateways) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Gateways</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Name</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Interface</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Gateway</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Monitor IP</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($gateways as $gateway)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap font-bold">
                                                {{ $gateway['name'] ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap uppercase">
                                                {{ $gateway['interface'] ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                                                {{ $gateway['gateway'] ?? $gateway['monitorip'] ?? 'Dynamic' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                                                {{ $gateway['monitorip'] ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Firewall Rules Summary --}}
            @if(isset($firewallRules['data']) && count($firewallRules['data']) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Recent Firewall Rules</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Interface</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Action</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Protocol</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Source</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Destination</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach(array_slice($firewallRules['data'], 0, 5) as $rule)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if(is_array($rule['interface']))
                                                    {{ implode(', ', $rule['interface']) }}
                                                @else
                                                    {{ $rule['interface'] ?? 'N/A' }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if(isset($rule['type']) && $rule['type'] === 'pass')
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Pass
                                                    </span>
                                                @elseif(isset($rule['type']) && $rule['type'] === 'block')
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Block
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ $rule['type'] ?? 'N/A' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $rule['protocol'] ?? 'any' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                                {{ $rule['source']['address'] ?? $rule['src'] ?? 'any' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                                {{ $rule['destination']['address'] ?? $rule['dst'] ?? 'any' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($firewallRules['data']) > 5)
                            <p class="mt-4 text-sm text-gray-500">Showing 5 of {{ count($firewallRules['data']) }} rules...</p>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>