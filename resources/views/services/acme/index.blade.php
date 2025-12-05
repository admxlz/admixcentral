<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ACME Certificates') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Tabs --}}
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a href="{{ route('services.acme.certificates', $firewall) }}"
                       class="{{ $tab === 'certificates' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Certificates
                    </a>
                    <a href="{{ route('services.acme.account-keys', $firewall) }}"
                       class="{{ $tab === 'account_keys' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Account Keys
                    </a>
                    <a href="{{ route('services.acme.settings', $firewall) }}"
                       class="{{ $tab === 'settings' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        General Settings
                    </a>
                </nav>
            </div>

            {{-- Validation Errors & Success Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                {{-- Account Keys Tab --}}
                @if($tab === 'account_keys')
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Account Keys</h3>
                        
                        {{-- Create Form --}}
                        <div x-data="{ open: false }" class="mb-6">
                            <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <span x-show="!open">Add Account Key</span>
                                <span x-show="open">Cancel</span>
                            </button>

                            <div x-show="open" class="mt-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <form action="{{ route('services.acme.account-keys.store', $firewall) }}" method="POST">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="name" :value="__('Name')" />
                                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required />
                                        </div>
                                        <div>
                                            <x-input-label for="descr" :value="__('Description')" />
                                            <x-text-input id="descr" class="block mt-1 w-full" type="text" name="descr" />
                                        </div>
                                        <div>
                                            <x-input-label for="email" :value="__('Email')" />
                                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" required />
                                        </div>
                                        <div>
                                            <x-input-label for="server" :value="__('ACME Server')" />
                                            <x-select-input id="server" name="server" class="block mt-1 w-full">
                                                <option value="letsencrypt-production-2">Let's Encrypt Production 2</option>
                                                <option value="letsencrypt-staging-2">Let's Encrypt Staging 2</option>
                                                <option value="google-production">Google Public CA</option>
                                                <option value="zerossl">ZeroSSL</option>
                                            </x-select-input>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="register" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                                            <span class="ml-2 text-gray-700 dark:text-gray-300">Create new account key (Register)</span>
                                        </label>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <x-primary-button>
                                            {{ __('Save & Register') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- List --}}
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Name</th>
                                        <th scope="col" class="px-6 py-3">Description</th>
                                        <th scope="col" class="px-6 py-3">Server</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accountKeys as $key)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ $key['name'] ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4">{{ $key['descr'] ?? '' }}</td>
                                            <td class="px-6 py-4">{{ $key['server'] ?? '' }}</td>
                                            <td class="px-6 py-4">
                                                {{-- Status might be inferred or explicit --}}
                                                N/A
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('services.acme.account-keys.destroy', ['firewall' => $firewall, 'id' => $key['id'] ?? $key['name']]) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td colspan="5" class="px-6 py-4 text-center">No Account Keys found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Certificates Tab --}}
                @if($tab === 'certificates')
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Certificates</h3>
                            {{-- Add Cert --}}
                            <div x-data="{ open: false }">
                                <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <span x-show="!open">Add Certificate</span>
                                    <span x-show="open">Cancel</span>
                                </button>
                                
                                {{-- Create Modal/Form --}}
                                <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                            <div class="absolute inset-0 bg-gray-500 opacity-75" @click="open = false"></div>
                                        </div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                            <form action="{{ route('services.acme.certificates.store', $firewall) }}" method="POST" class="p-6">
                                                @csrf
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Add Certificate</h3>
                                                
                                                <div class="space-y-4">
                                                    <div>
                                                        <x-input-label for="cert_name" :value="__('Name')" />
                                                        <x-text-input id="cert_name" class="block mt-1 w-full" type="text" name="name" required />
                                                    </div>
                                                    <div>
                                                        <x-input-label for="cert_descr" :value="__('Description')" />
                                                        <x-text-input id="cert_descr" class="block mt-1 w-full" type="text" name="descr" />
                                                    </div>
                                                    <div>
                                                        <x-input-label for="status" :value="__('Status')" />
                                                        <x-select-input id="status" name="status" class="block mt-1 w-full">
                                                            <option value="active">Active</option>
                                                            <option value="disabled">Disabled</option>
                                                        </x-select-input>
                                                    </div>
                                                    <div>
                                                        <x-input-label for="acme_account_key" :value="__('Account Key')" />
                                                        <x-select-input id="acme_account_key" name="acme_account_key" class="block mt-1 w-full" required>
                                                            <option value="">Select Account Key</option>
                                                            @foreach($accountKeys as $key)
                                                                <option value="{{ $key['id'] ?? $key['name'] }}">{{ $key['name'] }}</option>
                                                            @endforeach
                                                        </x-select-input>
                                                    </div>
                                                    
                                                    {{-- Basic Domain Info - For robust implementation, this needs to be a dynamic list allowing multiple domains --}}
                                                    <div>
                                                        <x-input-label for="domainlist" :value="__('Domain Name')" />
                                                        <x-text-input id="domainlist" class="block mt-1 w-full" type="text" name="domainlist[]" placeholder="example.com" required />
                                                        <p class="text-sm text-gray-500 mt-1">Simple single domain entry for MVP.</p>
                                                    </div>

                                                </div>
                                                
                                                <div class="mt-6 flex justify-end space-x-3">
                                                    <button type="button" @click="open = false" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                                                        Cancel
                                                    </button>
                                                    <x-primary-button>
                                                        {{ __('Save') }}
                                                    </x-primary-button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- List --}}
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Name</th>
                                        <th scope="col" class="px-6 py-3">Account</th>
                                        <th scope="col" class="px-6 py-3">Last Renewal</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($certificates as $cert)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ $cert['name'] ?? 'N/A' }}
                                                <div class="text-xs text-gray-500">{{ $cert['descr'] ?? '' }}</div>
                                            </td>
                                            <td class="px-6 py-4">{{ $cert['acme_account_key'] ?? 'N/A' }}</td>
                                            <td class="px-6 py-4">{{ $cert['last_renewal'] ?? 'Never' }}</td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ ($cert['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($cert['status'] ?? 'unknown') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-2">
                                                <form action="{{ route('services.acme.certificates.issue', $firewall) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $cert['id'] }}">
                                                    <button type="submit" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Issue</button>
                                                </form>
                                                <form action="{{ route('services.acme.certificates.renew', $firewall) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $cert['id'] }}">
                                                    <button type="submit" class="font-medium text-green-600 dark:text-green-500 hover:underline">Renew</button>
                                                </form>
                                                <form action="{{ route('services.acme.certificates.destroy', ['firewall' => $firewall, 'id' => $cert['id']]) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td colspan="5" class="px-6 py-4 text-center">No Certificates found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Settings Tab --}}
                @if($tab === 'settings')
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">General Settings</h3>
                        <form action="{{ route('services.acme.settings.update', $firewall) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="cron_entry" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ isset($settings['cron_entry']) ? 'checked' : '' }}>
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Enable Cron Entry for Auto-Renewal</span>
                                    </label>
                                    <p class="text-sm text-gray-500 mt-1 ml-6">Automatically attempts to renew certificates that are expiring.</p>
                                </div>
                                
                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="write_certificates" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ isset($settings['write_certificates']) ? 'checked' : '' }}>
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Write Certificates to Configuration</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end">
                                <x-primary-button>
                                    {{ __('Update Settings') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                @endif
                
            </div>
        </div>
    </div>
</x-app-layout>
