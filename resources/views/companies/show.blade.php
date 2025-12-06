<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $company->name }}
            </h2>
            @if(auth()->user()->isGlobalAdmin())
                <a href="{{ route('companies.edit', $company) }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Edit Company
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Company Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Details</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $company->description ?? 'No description provided.' }}
                    </p>
                </div>
            </div>

            <!-- Users Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Users</h3>
                        <a href="{{ route('users.create', ['company_id' => $company->id]) }}"
                            class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded">
                            Add User
                        </a>
                    </div>

                    @if($company->users->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Name</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Email</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Role</th>
                                        <th
                                            class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($company->users as $user)
                                        <tr>
                                            <td class="px-4 py-2">{{ $user->name }}</td>
                                            <td class="px-4 py-2">{{ $user->email }}</td>
                                            <td class="px-4 py-2">{{ $user->role }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <a href="{{ route('users.edit', $user) }}"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-2">Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No users assigned to this company.</p>
                    @endif
                </div>
            </div>

            <!-- Firewalls Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Firewalls</h3>
                        <a href="{{ route('firewalls.create', ['company_id' => $company->id]) }}"
                            class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded">
                            Add Firewall
                        </a>
                    </div>

                    @if($company->firewalls->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Name</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            URL</th>
                                        <th
                                            class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($company->firewalls as $firewall)
                                        <tr>
                                            <td class="px-4 py-2">
                                                <a href="{{ route('firewall.dashboard', $firewall) }}"
                                                    class="text-blue-600 hover:underline font-semibold">
                                                    {{ $firewall->name }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-2">{{ $firewall->url }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <a href="{{ route('firewalls.edit', $firewall) }}"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No firewalls assigned to this company.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>