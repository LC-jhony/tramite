<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    <script>
        (() => {
            const themeStorageKey = 'theme';
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)');

            const isDarkTheme = () => {
                const theme = localStorage.getItem(themeStorageKey) ?? 'system';

                return theme === 'dark' || (theme === 'system' && systemPrefersDark.matches);
            };

            const applyTheme = () => {
                document.documentElement.classList.toggle('dark', isDarkTheme());
            };

            window.toggleTheme = () => {
                const nextTheme = isDarkTheme() ? 'light' : 'dark';

                localStorage.setItem(themeStorageKey, nextTheme);

                // Mantiene sincronizado el store `theme` de Alpine de Filament
                // si el JS del panel está cargado en la misma página.
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: nextTheme }));

                applyTheme();
            };

            applyTheme();

            // Con la navegación SPA de Livewire el <head> se reutiliza: registra los
            // listeners una sola vez para no duplicarlos en cada visita.
            if (! window.themeListenersRegistered) {
                window.themeListenersRegistered = true;

                window.addEventListener('theme-changed', applyTheme);
                systemPrefersDark.addEventListener('change', applyTheme);
                document.addEventListener('livewire:navigated', applyTheme);
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @filamentStyles()
</head>

<body class="bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100">
    @php
        $isRegisterDocumentActive = request()->routeIs('register.document');
    @endphp

    <nav class="bg-primary-600 shadow dark:bg-primary-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a href="/" class="shrink-0">
                        <span class="text-xl font-bold text-white">{{ config('app.name') }}</span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <x-filament::link :href="route('register.document')" :weight="$isRegisterDocumentActive ? 'semibold' : null" :aria-current="$isRegisterDocumentActive ? 'page' : null" color="gray" icon="heroicon-m-sparkles">
                                Registrar Tramite
                            </x-filament::link>
                        </div>
                    </div>

                    <button
                        type="button"
                        x-data
                        x-on:click="toggleTheme()"
                        class="rounded-lg p-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                        aria-label="Cambiar entre modo claro y oscuro"
                        title="Cambiar entre modo claro y oscuro"
                    >
                        <x-filament::icon icon="heroicon-m-sun" class="hidden dark:block" />
                        <x-filament::icon icon="heroicon-m-moon" class="dark:hidden" />
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <x-container>
        {{ $slot }}
    </x-container>
    @livewireScripts
    @filamentScripts()
</body>

</html>
