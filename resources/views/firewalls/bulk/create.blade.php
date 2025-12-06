<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Bulk Add: ') . ucfirst($type) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Adding {{ $type }} to {{ count(explode(',', $firewall_ids)) }} firewalls.
                    </p>

                    <form action="{{ route('firewalls.bulk.store', $type) }}" method="POST">
                        @csrf
                        <input type="hidden" name="firewall_ids" value="{{ $firewall_ids }}">

                        @if($type === 'alias')
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="name">Name</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="name" type="text" name="name" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="type">Type</label>
                                    <select
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="type" name="type">
                                        <option value="host">Host(s)</option>
                                        <option value="network">Network(s)</option>
                                        <option value="port">Port(s)</option>
                                        <option value="url">URL (IPs)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="address">Content (IPs/Networks/Ports)</label>
                                    <textarea
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="address" name="address" rows="3"></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Space or comma separated.</p>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="descr">Description</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="descr" type="text" name="descr">
                                </div>
                            </div>

                        @elseif($type === 'nat')
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="interface">Interface</label>
                                    <select
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="interface" name="interface">
                                        <option value="wan">WAN</option>
                                        <option value="lan">LAN</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="protocol">Protocol</label>
                                    <select
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="protocol" name="protocol">
                                        <option value="tcp">TCP</option>
                                        <option value="udp">UDP</option>
                                        <option value="tcp/udp">TCP/UDP</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="dstport">Destination Port (External)</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="dstport" type="text" name="dstport" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="target">Target IP (Internal)</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="target" type="text" name="target" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="local-port">Target Port (Internal)</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="local-port" type="text" name="local-port" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="descr">Description</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="descr" type="text" name="descr">
                                </div>
                            </div>

                        @elseif($type === 'rule')
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="type">Action</label>
                                    <select
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="type" name="type">
                                        <option value="pass">Pass</option>
                                        <option value="block">Block</option>
                                        <option value="reject">Reject</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="interface">Interface</label>
                                    <select
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="interface" name="interface">
                                        <option value="wan">WAN</option>
                                        <option value="lan">LAN</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="protocol">Protocol</label>
                                    <select
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="protocol" name="protocol">
                                        <option value="tcp">TCP</option>
                                        <option value="udp">UDP</option>
                                        <option value="tcp/udp">TCP/UDP</option>
                                        <option value="any">Any</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            for="src">Source</label>
                                        <input
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                            id="src" type="text" name="src" value="any" placeholder="IP/Alias or 'any'">
                                    </div>
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            for="srcport">Source Port</label>
                                        <input
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                            id="srcport" type="text" name="srcport" placeholder="Port or 'any'">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            for="dst">Destination</label>
                                        <input
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                            id="dst" type="text" name="dst" value="any" placeholder="IP/Alias or 'any'">
                                    </div>
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            for="dstport">Destination Port</label>
                                        <input
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                            id="dstport" type="text" name="dstport" placeholder="Port or 'any'">
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="descr">Description</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="descr" type="text" name="descr">
                                </div>
                            </div>

                        @elseif($type === 'ipsec')
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="remote-gateway">Remote Gateway</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="remote-gateway" type="text" name="remote-gateway" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="pre-shared-key">Pre-Shared Key</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="pre-shared-key" type="text" name="pre-shared-key" required>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            for="myid_type">My Identifier Type</label>
                                        <select
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                            id="myid_type" name="myid_type">
                                            <option value="myaddress">My IP Address</option>
                                            <option value="fqdn">FQDN</option>
                                            <option value="user_fqdn">User FQDN (Email)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            for="myid_data">My Identifier Value</label>
                                        <input
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                            id="myid_data" type="text" name="myid_data">
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        for="descr">Description</label>
                                    <input
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                        id="descr" type="text" name="descr">
                                </div>
                            </div>
                        @else
                            <p class="text-red-500">Unknown Bulk Action Type: {{ $type }}</p>
                        @endif

                        <div class="mt-6 flex items-center justify-end">
                            <a href="{{ route('firewalls.index') }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">Cancel</a>
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Push to Firewalls
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>