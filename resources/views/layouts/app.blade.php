<!DOCTYPE html>
@php
    $settings = \App\Models\SystemSetting::pluck('value', 'key')->toArray();
    $theme = $settings['theme'] ?? 'light';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="{{ $theme === 'dark' ? 'dark' : '' }} h-full bg-gray-100 dark:bg-gray-900 overflow-hidden">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(isset($settings['favicon_path']))
        <link rel="icon" href="{{ $settings['favicon_path'] }}">
    @endif

    <style>
        .form-input-reset {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background-color: transparent !important;
        }
    </style>
</head>

<body class="font-sans antialiased h-full overflow-hidden bg-gray-100 dark:bg-gray-900" x-data="{ sidebarOpen: false, collapsed: false }">
    <!-- Mobile Header -->
    <div class="md:hidden flex items-center justify-between h-16 bg-gray-900 border-b border-gray-700 px-4 z-40 relative">
        <a href="{{ route('dashboard') }}">
             @if(isset($settings['logo_path']))
                <img src="{{ $settings['logo_path'] }}" class="block h-8 w-auto" alt="Logo">
            @else
                <img src="{{ asset('images/logo.png') }}" class="block h-8 w-auto" alt="Logo">
            @endif
        </a>
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white focus:outline-none">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <div class="flex h-screen bg-gray-100 dark:bg-gray-900 md:h-screen h-[calc(100vh-4rem)]">
        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow z-10">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            @if(request()->route('firewall'))
                @include('layouts.navigation')
            @endif

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900">

                <!-- Page Content -->
                @isset($firewall)
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                        <x-apply-changes-banner :firewall="$firewall" />
                    </div>
                @endisset


                {{ $slot }}
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: "{{ session('error') }}",
                    toast: true, // Set to false if you want a modal instead of a toast
                    position: 'top-end', // Center if modal
                    showConfirmButton: true,
                    // timer: 5000 
                });
            @endif
        });
    </script>


</body>

</html>