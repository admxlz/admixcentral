<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('firewalls.create') }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add Firewall
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Firewalls</h3>

                    @if($firewallsWithStatus->isEmpty())
                        <p class="text-gray-500">No firewalls configured yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($firewallsWithStatus as $firewall)
                                <div x-data="{
                                            loading: true,
                                            online: false,
                                            status: null,
                                            error: null,
                                            init() {
                                                fetch('{{ route('firewall.check-status', $firewall) }}')
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        this.loading = false;
                                                        this.online = data.online;
                                                        if (data.online) {
                                                            this.status = data.status;
                                                        } else {
                                                            this.error = data.error;
                                                        }
                                                    })
                                                    .catch(error => {
                                                        this.loading = false;
                                                        this.online = false;
                                                        this.error = 'Failed to check status';
                                                    });
                                            }
                                        }" class="border dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-gray-800 hover:shadow-lg transition">
                                    
                                    {{-- Header Row: Name & Actions --}}
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-3">
                                            <h4 class="font-bold text-xl">{{ $firewall->name }}</h4>
                                            
                                            <template x-if="loading">
                                                <span class="bg-gray-200 text-gray-800 text-xs px-2.5 py-0.5 rounded animate-pulse">Loading...</span>
                                            </template>
                                            <template x-if="!loading && online">
                                                <span class="bg-green-100 text-green-800 text-xs px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Online</span>
                                            </template>
                                            <template x-if="!loading && !online">
                                                <span class="bg-red-100 text-red-800 text-xs px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Offline</span>
                                            </template>

                                             @if(auth()->user()->role === 'admin')
                                                <span class="text-xs text-gray-500 border-l pl-3 dark:border-gray-600">{{ $firewall->company->name }}</span>
                                            @endif
                                            <span class="text-xs text-gray-400 border-l pl-3 dark:border-gray-600 font-mono">{{ $firewall->url }}</span>
                                        </div>

                                        <div class="flex gap-2">
                                            <a href="{{ route('firewall.dashboard', $firewall) }}"
                                                class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded">
                                                Manage
                                            </a>
                                            <a href="{{ route('firewalls.edit', $firewall) }}"
                                                class="bg-gray-500 hover:bg-gray-700 text-white text-sm font-bold py-2 px-4 rounded">
                                                Edit
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Content Area --}}
                                    <div class="min-h-[6rem]">
                                        <template x-if="loading">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-pulse">
                                                <div class="space-y-2">
                                                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                                                </div>
                                                <div class="space-y-2">
                                                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                                                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                                                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!loading && online && status && status.data">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                                {{-- Left Column: System Details Table --}}
                                                <div>
                                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                                        <tbody>
                                                            <tr class="border-b dark:border-gray-700">
                                                                <th class="py-1 font-medium text-gray-900 dark:text-gray-300">Version</th>
                                                                <td class="py-1" x-text="status.data.version"></td>
                                                            </tr>
                                                            <tr class="border-b dark:border-gray-700">
                                                                <th class="py-1 font-medium text-gray-900 dark:text-gray-300">Platform</th>
                                                                <td class="py-1" x-text="status.data.platform"></td>
                                                            </tr>
                                                            <tr class="border-b dark:border-gray-700">
                                                                <th class="py-1 font-medium text-gray-900 dark:text-gray-300">BIOS</th>
                                                                <td class="py-1">
                                                                    <div class="flex flex-col text-xs">
                                                                        <span x-text="status.data.bios_vendor"></span>
                                                                        <span x-text="status.data.bios_version"></span>
                                                                        <span x-text="status.data.bios_date"></span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b dark:border-gray-700">
                                                                <th class="py-1 font-medium text-gray-900 dark:text-gray-300">CPU System</th>
                                                                <td class="py-1">
                                                                    <div class="flex flex-col text-xs">
                                                                        <span x-text="status.data.cpu_model"></span>
                                                                        <span class="text-gray-500" x-text="(status.data.cpu_count || '1') + ' CPUs'"></span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr class="dark:border-gray-700">
                                                                <th class="py-1 font-medium text-gray-900 dark:text-gray-300">Uptime</th>
                                                                <td class="py-1" x-text="status.data.uptime"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                {{-- Right Column: Mini Graphs --}}
                                                <div class="space-y-3">
                                                    {{-- CPU --}}
                                                    <div>
                                                        <div class="flex justify-between mb-1 text-xs">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">CPU Usage</span>
                                                            <span class="text-gray-700 dark:text-gray-300" x-text="(status.data.cpu_usage || 0) + '%'"></span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" :style="'width: ' + (status.data.cpu_usage || 0) + '%'"></div>
                                                        </div>
                                                    </div>

                                                    {{-- Memory --}}
                                                    <div>
                                                        <div class="flex justify-between mb-1 text-xs">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">Memory Usage</span>
                                                            <span class="text-gray-700 dark:text-gray-300" x-text="(status.data.mem_usage || 0) + '%'"></span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                            <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" :style="'width: ' + (status.data.mem_usage || 0) + '%'"></div>
                                                        </div>
                                                    </div>

                                                    {{-- Swap --}}
                                                    <div>
                                                        <div class="flex justify-between mb-1 text-xs">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">Swap Usage</span>
                                                            <span class="text-gray-700 dark:text-gray-300" x-text="(status.data.swap_usage || 0) + '%'"></span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                            <div class="bg-red-600 h-2 rounded-full transition-all duration-500" :style="'width: ' + (status.data.swap_usage || 0) + '%'"></div>
                                                        </div>
                                                    </div>

                                                    {{-- Disk --}}
                                                    <div>
                                                        <div class="flex justify-between mb-1 text-xs">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">Disk Usage (/)</span>
                                                            <span class="text-gray-700 dark:text-gray-300" x-text="(status.data.disk_usage || 0) + '%'"></span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                            <div class="bg-yellow-600 h-2 rounded-full transition-all duration-500" :style="'width: ' + (status.data.disk_usage || 0) + '%'"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!loading && !online">
                                            <div class="text-center py-6">
                                                <p class="text-red-500 font-semibold">Firewall is unreachable</p>
                                                <p class="text-sm text-gray-500" x-text="error || 'Connection timed out or refused'"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>