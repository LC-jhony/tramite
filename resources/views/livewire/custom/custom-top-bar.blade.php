<header class="bg-primary-600 pb-52 text-white shadow dark:bg-primary-700">
    <div class="fi-topbar-ctn">
        @php
            use Filament\Support\Facades\FilamentView;
            use Filament\View\PanelsRenderHook;
            use function Filament\Support\generate_href_html;
            $navigation = filament()->getNavigation();
            $hasNavigation = filament()->hasNavigation();
            $hasTopNavigation = filament()->hasTopNavigation();
        @endphp

        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_START) }}

            {{-- Left: Logo + Sidebar Toggles + Navigation --}}
            <div class="flex items-center space-x-8">
                <x-topbar.sidebar-toggles />

                {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_LOGO_BEFORE) }}

                {{-- Logo --}}
                @if ($homeUrl = filament()->getHomeUrl())
                    <a {{ generate_href_html($homeUrl) }} class="inline-flex items-center">
                        <x-filament-panels::logo class="h-8 w-auto text-white" />
                    </a>
                @else
                    <x-filament-panels::logo class="h-8 w-auto text-white" />
                @endif

                {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_LOGO_AFTER) }}

                {{-- Tenant Menu --}}
                @if (filament()->hasTenancy())
                    <x-filament-panels::tenant-menu />
                @endif

                {{-- Navigation - Desktop only --}}
                @if ($hasTopNavigation && $hasNavigation)
                    <nav class="hidden items-center space-x-1 text-sm font-medium lg:flex">
                        @foreach ($navigation as $group)
                            @if ($group->getLabel())
                                {{-- Group with label: render as dropdown --}}
                                <x-topbar.nav-dropdown :group="$group" />
                            @else
                                {{-- Group without label: render items directly --}}
                                @foreach ($group->getItems() as $item)
                                    <x-topbar.navigation-item :item="$item" variant="pill" :include-child-active-state="false" />
                                @endforeach
                            @endif
                        @endforeach
                    </nav>
                @endif
            </div>

            {{-- Right: Search + Notifications + User Menu --}}
            <x-topbar.actions />

            {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_END) }}
        </div>
    </div>

    <x-filament-actions::modals />
</header>
