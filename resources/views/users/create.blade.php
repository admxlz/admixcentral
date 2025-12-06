<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                            <input type="password" name="password" id="password" required
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm
                                Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Role -->
                        <div class="mb-4">
                            <label for="role"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                            <select name="role" id="role"
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User (Standard Access)
                                </option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Full Access
                                    within Scope)</option>
                            </select>
                            @error('role')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Company -->
                        <div class="mb-4">
                            <label for="company_id"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                            @if($companies->count() > 1 || auth()->user()->isGlobalAdmin())
                                <select name="company_id" id="company_id"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">None (Global / MSP - Admin Only)</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ (old('company_id') == $company->id || request('company_id') == $company->id) ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Select "None" to create a Global Admin (must select
                                    Admin role).</p>
                            @else
                                <!-- Single company available (Company Admin) -->
                                <input type="hidden" name="company_id" value="{{ $companies->first()->id }}">
                                <input type="text" disabled value="{{ $companies->first()->name }}"
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            @endif
                            @error('company_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create User
                            </button>
                            <a href="{{ route('users.index') }}"
                                class="text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>