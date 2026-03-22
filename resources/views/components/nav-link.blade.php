@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'flex item-center gap-2 block px-4 py-2 mt-2 text-sm font-semibold text-white bg-primary-900/15 rounded-lg dark:bg-primary-700 dark:hover:bg-primary-600 dark:focus:bg-primary-600 dark:focus:text-white dark:hover:text-white dark:text-primary-200 hover:text-white/80 focus:text-primary-900 hover:bg-primary-900/10 focus:bg-primary-200 focus:outline-none focus:shadow-outline'
            : 'flex item-center gap-2 block px-4 py-2 mt-2 text-sm font-semibold text-white bg-transparent rounded-lg dark:bg-transparent dark:hover:bg-primary-600 dark:focus:bg-primary-600 dark:focus:text-white dark:hover:text-white dark:text-primary-200 hover:text-primary-900 focus:text-primary-900 hover:bg-primary-900/10 focus:bg-primary-200 focus:outline-none focus:shadow-outline';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
