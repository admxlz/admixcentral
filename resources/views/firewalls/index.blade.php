<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Firewalls') }}
            </h2>
            <div class="mt-1 flex flex-col sm:flex-row sm:items-center">
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                     <a href="{{ route('dashboard') }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors duration-200">
                        {{ __('Dashboard') }}
                     </a>
                     <span class="mx-2 text-gray-300 dark:text-gray-600">/</span>
                     <span class="font-medium">
                        {{ __('Firewalls') }}
                     </span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-500 text-white p-4 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('bulk_results'))
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Bulk Action Results:</p>
                    <ul class="list-disc pl-5">
                        @foreach(session('bulk_results') as $result)
                            <li>{{ $result }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                 $uniqueCustomers = $firewalls->pluck('company.name')->unique()->sort()->values();
            @endphp
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-data="{
                deleteModalOpen: false,
                deleteAction: '',
                firewallName: '',
                confirmEmail: '',
                search: '',
                statusFilter: 'all',
                customerFilter: 'all',
                customers: {{ json_encode($uniqueCustomers) }},
                
                openDeleteModal(action, name) {
                    this.deleteAction = action;
                    this.firewallName = name;
                    this.confirmEmail = '';
                    $dispatch('open-modal', 'delete-firewall-modal');
                },

                get filteredCustomers() {
                    if (this.search === '') return this.customers;
                    return this.customers.filter(c => c.toLowerCase().includes(this.search.toLowerCase()));
                }
            }">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col space-y-4 mb-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Managed Firewalls') }}
                            </h3>
                            <a href="{{ route('firewalls.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                {{ __('Add Firewall') }}
                            </a>
                        </div>

                        <!-- Toolbar -->
                        <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg mb-6">
                            
                            <!-- Left Side: Filters & Search (Flex Grow) -->
                            <div class="flex flex-col sm:flex-row gap-4 w-full lg:flex-1">
                                <!-- Search (Expandable) -->
                                <div class="relative w-full sm:w-64 lg:w-auto lg:flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        x-model="search"
                                        placeholder="Search firewalls..." 
                                        class="pl-10 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    >
                                    <button 
                                        x-show="search.length > 0" 
                                        @click="search = ''" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none"
                                        style="display: none;"
                                    >
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Filters Group -->
                                <div class="flex gap-2 w-full sm:w-auto shrink-0">
                                    <!-- Customer Filter -->
                                    <div class="relative w-1/2 sm:w-auto min-w-[160px]" x-data="{ open: false, filter: '' }">
                                        <button @click="open = !open" type="button" 
                                            class="flex items-center justify-between w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <span x-text="customerFilter === 'all' ? 'All Customers' : customerFilter" class="truncate block text-left"></span>
                                            <svg class="h-4 w-4 ml-2 text-gray-500 transform transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-transition class="absolute z-10 mt-1 w-full sm:w-[200px] bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                            <div class="p-2 border-b dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                                                <input x-model="filter" type="text" placeholder="Search..." class="w-full text-xs rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                                            </div>
                                            <div @click="customerFilter = 'all'; open = false" class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm">All Customers</div>
                                            <template x-for="c in customers.filter(x => x.toLowerCase().includes(filter.toLowerCase()))" :key="c">
                                                <div @click="customerFilter = c; open = false" class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm truncate" x-text="c"></div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Status Filter -->
                                    <select x-model="statusFilter" class="w-1/2 sm:w-auto rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="all">All Status</option>
                                        <option value="online">Online</option>
                                        <option value="offline">Offline</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Right Side: Bulk Actions -->
                            <div class="flex gap-2 w-full lg:w-auto items-center border-t lg:border-t-0 lg:border-l lg:pl-4 pt-4 lg:pt-0 border-gray-200 dark:border-gray-600">
                                <span class="text-sm text-gray-500 whitespace-nowrap hidden xl:inline">With selected:</span>
                                <div class="flex gap-2 w-full">
                                    <select id="bulkActionSelect" name="action" form="bulkForm"
                                        class="block w-full lg:w-48 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 sm:text-sm">
                                        <option value="">Bulk Actions...</option>
                                        <optgroup label="System">
                                            <option value="reboot">Reboot</option>
                                            <option value="update">System Update</option>
                                            <option value="update_rest_api">Update REST API</option>
                                            <option value="create_package">Install Package</option>
                                        </optgroup>
                                        <optgroup label="Configuration (Add to All)">
                                            <option value="create_alias">Add Alias</option>
                                            <option value="create_nat">Add NAT 1:1 / Port Forward</option>
                                            <option value="create_rule">Add Firewall Rule</option>
                                            <option value="create_ipsec">Add IPSec Tunnel</option>
                                        </optgroup>
                                    </select>
                                    <button type="button" onclick="submitBulkAction()"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-150">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>

                        <form id="bulkForm" action="{{ route('firewalls.bulk.action') }}" method="POST">
                            @csrf
                            {{-- Hidden form, inputs will reference it by ID --}}
                        </form>

                    <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Host
                                </th>
                                @if(auth()->user()->isGlobalAdmin())
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Company
                                    </th>
                                @endif
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($firewalls as $firewall)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition" 
                                    x-data="firewallRow(
                                        {{ $firewall->id }}, 
                                        {{ json_encode($firewall->cached_status) }}, 
                                        '{{ route('firewall.check-status', $firewall) }}',
                                        '{{ addslashes(strtolower($firewall->name . ' ' . $firewall->company->name . ' ' . $firewall->url . ' ' . $firewall->hostname)) }}',
                                        '{{ addslashes($firewall->company->name) }}'
                                    )"
                                    x-show="checkVisibility(search, statusFilter, customerFilter)"
                                    style="">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="firewall_ids[]" value="{{ $firewall->id }}"
                                            form="bulkForm" class="firewall-checkbox">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $firewall->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <template x-if="loading">
                                            <div class="h-5 w-16 bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse"></div>
                                        </template>
                                        <template x-if="!loading && isOnline">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Online
                                            </span>
                                        </template>
                                        <template x-if="!loading && !isOnline">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Offline
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $firewall->url }}
                                    </td>
                                    @if(auth()->user()->isGlobalAdmin())
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <a href="{{ route('companies.show', $firewall->company) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300">
                                                {{ $firewall->company->name }}
                                            </a>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-3">
                                            <a href="{{ route('firewall.dashboard', $firewall) }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                Manage
                                            </a>
                                            <a href="{{ route('firewalls.edit', $firewall) }}"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                Edit
                                            </a>
                                            <button type="button"
                                                @click="openDeleteModal('{{ route('firewalls.destroy', $firewall) }}', '{{ addslashes($firewall->name) }}')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->isGlobalAdmin() ? '6' : '5' }}" class="px-6 py-4 text-center text-gray-500">
                                        No firewalls found. <a href="{{ route('firewalls.create') }}"
                                            class="text-blue-600 hover:underline">Add one now</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <x-modal name="delete-firewall-modal" :show="false" focusable>
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Delete Firewall') }}
                            </h2>
            
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Are you sure you want to delete the firewall ') }} <span class="font-bold" x-text="firewallName"></span>? {{ __('This action cannot be undone.') }}
                            </p>
                            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Please type your email address to confirm:') }} <span class="font-mono font-bold">{{ auth()->user()->email }}</span>
                            </p>
            
                            <form :action="deleteAction" method="POST" id="delete-firewall-form">
                                @csrf
                                @method('DELETE')
                                <div class="mt-6">
                                    <x-input-label for="confirm_email" value="{{ __('Email Address') }}" class="sr-only" />
            
                                    <x-text-input
                                        id="confirm_email"
                                        name="confirm_email"
                                        type="email"
                                        class="mt-1 block w-3/4"
                                        placeholder="{{ __('Email Address') }}"
                                        x-model="confirmEmail"
                                        @keyup.enter="if(confirmEmail === '{{ auth()->user()->email }}') document.getElementById('delete-firewall-form').submit()"
                                    />
                                </div>
            
                                <div class="mt-6 flex justify-end">
                                    <x-secondary-button @click="$dispatch('close')">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>
            
                                    <x-danger-button class="ml-3"
                                        x-bind:disabled="confirmEmail !== '{{ auth()->user()->email }}'"
                                        x-bind:class="{ 'opacity-50 cursor-not-allowed': confirmEmail !== '{{ auth()->user()->email }}' }"
                                        @click="document.getElementById('delete-firewall-form').submit()">
                                        {{ __('Delete Firewall') }}
                                    </x-danger-button>
                                </div>
                            </form>
                        </div>
                    </x-modal>

                    {{-- Re-implement Delete Buttons properly since they were inside the form --}}
                    {{-- Actually, delete forms need to be outside. --}}
                    {{-- I will fix the wrapping in the next step or adjust now. --}}
                    {{-- Strategy: Use JS for Bulk submit, don't wrap table in form. --}}

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('firewallRow', (id, statusData, checkPath, searchData, companyName) => ({
                id: id,
                isOnline: statusData && statusData.online,
                loading: !statusData,
                searchData: searchData,
                companyName: companyName,

                checkVisibility(search, statusFilter, customerFilter) {
                   search = (search || '').toLowerCase();
                   const matchesSearch = search === '' || this.searchData.includes(search);
                   
                   const matchesStatus = statusFilter === 'all' 
                        || (statusFilter === 'online' && this.isOnline)
                         || (statusFilter === 'offline' && !this.isOnline);

                   const matchesCustomer = customerFilter === 'all' || this.companyName === customerFilter;

                   return matchesSearch && matchesStatus && matchesCustomer;
                },

                init() {
                    // Check status immediately (silent)
                    this.checkStatus(checkPath);

                    // Poll every 10 seconds (silent)
                    setInterval(() => this.checkStatus(checkPath), 10000);

                    // Listen for real-time updates
                    if (window.Echo) {
                        window.Echo.private('firewall.' + this.id)
                            .listen('.firewall.status.update', (e) => {
                                // console.log('Row Update ' + this.id, e);
                                this.isOnline = true; 
                                this.loading = false;
                            });
                    }
                },

                checkStatus(url) {
                    // Don't set this.loading = true here to avoid flickering "Checking..."
                    // this.loading = true; 
                    
                    fetch(url + '?t=' + Date.now())
                        .then(res => res.json())
                        .then(data => {
                            this.isOnline = data.online;
                            this.loading = false; // Just in case it was true
                        })
                        .catch(() => {
                            // If request fails, likely network issue or offline.
                            this.isOnline = false;
                            this.loading = false;
                        });
                }
            }));
        });

        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.firewall-checkbox');
            const selectAll = document.getElementById('selectAll');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }

        function submitBulkAction() {
            const action = document.getElementById('bulkActionSelect').value;
            if (!action) {
                alert('Please select an action.');
                return;
            }

            const checkboxes = document.querySelectorAll('.firewall-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);

            if (ids.length === 0) {
                alert('Please select at least one firewall.');
                return;
            }

            if (action.startsWith('create_')) {
                // Redirect to create page
                const type = action.replace('create_', '');
                const url = `{{ url('/firewalls/bulk/create') }}/${type}`;
                // Append IDs
                const queryString = ids.map(id => `firewall_ids[]=${id}`).join('&');
                window.location.href = `${url}?${queryString}`;
            } else {
                // POST action (reboot/update)
                if (action === 'reboot') {
                    if (!confirm('WARNING: Are you sure you want to REBOOT the selected firewalls? usage of this command will cause network downtime.')) {
                        return;
                    }
                } else if (!confirm('Are you sure you want to perform this action on selected firewalls?')) {
                    return;
                }
                const form = document.getElementById('bulkForm');
                form.submit();
            }
        }
    </script>
</x-app-layout>