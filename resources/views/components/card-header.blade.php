@props(['title'])

<div class="flex justify-between items-center mb-4">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $title }}</h3>
    <div class="flex items-center space-x-2">
        {{ $slot }}
    </div>
</div>
