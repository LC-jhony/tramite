<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body @class(['antialiased'])>

    <x-navbar>
        <x-navlink href="{{ route('document.form') }}" :active="request()->routeIs('document.form')">
            {{ __('Document Form') }}
        </x-navlink>
        <x-navlink href="{{ route('case.tracking.form') }}" :active="request()->routeIs('case.tracking.form')">
            {{ __('Case Tracking Form') }}
        </x-navlink>
        <x-navlink href="{{ route('filament.admin.auth.login') }}" :active="request()->routeIs('filament.admin.auth.login')">
            {{ __('Admin Login') }}
        </x-navlink>
    </x-navbar>
    <x-container>
        {{ $slot }}
    </x-container>

    @livewire('notifications') {{-- Only required if you wish to send flash notifications --}}

    @filamentScripts
    @vite('resources/js/app.js')
</body>

</html>
