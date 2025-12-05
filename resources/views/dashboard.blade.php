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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                                        }" class="border dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-lg">{{ $firewall->name }}</h4>

                                        <template x-if="loading">
                                            <span
                                                class="bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded animate-pulse">Loading...</span>
                                        </template>

                                        <template x-if="!loading && online">
                                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded">Online</span>
                                        </template>

                                        <template x-if="!loading && !online">
                                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded">Offline</span>
                                        </template>
                                    </div>

                                    @if(auth()->user()->role === 'admin')
                                        <p class="text-sm text-gray-500 mb-2">{{ $firewall->company->name }}</p>
                                    @endif

                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $firewall->url }}</p>

                                    <div class="min-h-[3rem]">
                                        <template x-if="loading">
                                            <div class="space-y-2 animate-pulse">
                                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                            </div>
                                        </template>

                                        <template x-if="!loading && online && status && status.data">
                                            <div class="text-sm space-y-1">
                                                <template x-if="status.data.platform">
                                                    <p><span class="font-semibold">Platform:</span> <span
                                                            x-text="status.data.platform"></span></p>
                                                </template>
                                                <template x-if="status.data.version">
                                                    <p><span class="font-semibold">Version:</span> <span
                                                            x-text="status.data.version"></span></p>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="!loading && !online">
                                            <p class="text-sm text-red-500 break-words" x-text="error || 'Connection failed'">
                                            </p>
                                        </template>
                                    </div>

                                    <div class="mt-4 flex gap-2">
                                        <a href="{{ route('firewall.dashboard', $firewall) }}"
                                            class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-1 px-3 rounded">
                                            Manage
                                        </a>
                                        <a href="{{ route('firewalls.edit', $firewall) }}"
                                            class="bg-gray-500 hover:bg-gray-700 text-white text-sm font-bold py-1 px-3 rounded">
                                            Edit
                                        </a>
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