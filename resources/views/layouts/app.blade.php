<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body class="antialiased bg-zinc-100">
    {{-- <div class="fixed top-0 left-0 right-0 h-60 bg-primary-600 dark:bg-primary-700 -z-10 transition-colors"></div> --}}

    <x-navbar>
        <x-nav-link :href="route('create.tramite')" :active="request()->routeIs('create.tramite')">
            Registrar tramite
        </x-nav-link>
        <x-nav-link :href="route('consulta.document')" :active="request()->routeIs('consulta.document')">
            Realizar consulta
        </x-nav-link>
        <x-nav-link :href="route('filament.admin.auth.login')" :active="request()->routeIs('filament.admin.auth.login')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>

            Iniciar Sesión
        </x-nav-link>
    </x-navbar>

    <main>
        {{ $slot }}
    </main>

    @livewire('notifications') {{-- Only required if you wish to send flash notifications --}}

    @filamentScripts
    @vite('resources/js/app.js')
</body>

</html>