<nav class="mx-auto max-w-7xl sticky top-0 z-50 bg-primary-600 dark:bg-primary-700 text-white" x-data="{ open: false, userMenuOpen: false }">
    <div class="flex flex-col max-w-7xl px-4 mx-auto md:items-center md:justify-between md:flex-row md:px-6 lg:px-8 p-4">
        <div class="flex flex-row items-center justify-between gap-4">
            <x-application-logo />
            <a href="{{ route('create.tramite') }}"
                class="text-lg font-semibold tracking-widest text-white uppercase rounded-lg dark:text-white focus:outline-none focus:shadow-outline">
            Tramita Ya
            </a>

            <button class="rounded-lg md:hidden focus:outline-none focus:shadow-outline" @click="open = !open">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-6 h-6">
                    <path x-show="!open" fill-rule="evenodd"
                        d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM9 15a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1z"
                        clip-rule="evenodd"></path>
                    <path x-show="open" fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div :class="{ 'flex': open, 'hidden': !open }"
            class="flex-col flex-grow hidden pb-4 md:pb-0 md:flex md:justify-end md:flex-row gap-2">
            {{ $slot }}
        </div>
    </div>
</nav>
