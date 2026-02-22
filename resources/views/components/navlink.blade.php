@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'block px-4 py-2 text-sm font-semibold text-white bg-indigo-900/10 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500'
            : 'block px-4 py-2 text-sm font-semibold text-indigo-100 hover:text-white hover:bg-indigo-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
