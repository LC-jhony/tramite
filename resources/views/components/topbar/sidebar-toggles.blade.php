@php
    use Filament\Support\Icons\Heroicon;
    use Filament\View\PanelsIconAlias;
    $hasNavigation = filament()->hasNavigation();
    $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
    $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
    $isRtl = __('filament-panels::layout.direction') === 'rtl';
@endphp

{{-- Sidebar Toggle (Hamburger) - Mobile only --}}
@if ($hasNavigation)
    <div class="lg:hidden" x-data>
        <x-filament::icon-button color="gray" :icon="Heroicon::OutlinedBars3" :icon-alias="PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON" icon-size="lg" :label="__('filament-panels::layout.actions.sidebar.expand.label')"
            x-cloak x-on:click="$store.sidebar.open()" x-show="! $store.sidebar.isOpen"
            class="fi-topbar-open-sidebar-btn !text-white hover:!bg-white/20" />

        <x-filament::icon-button color="gray" :icon="Heroicon::OutlinedXMark" :icon-alias="PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON" icon-size="lg" :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
            x-cloak x-on:click="$store.sidebar.close()" x-show="$store.sidebar.isOpen"
            class="fi-topbar-close-sidebar-btn !text-white hover:!bg-white/20" />
    </div>
@endif

{{-- Sidebar Collapsible Toggle - Desktop only --}}
@if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
    <div class="hidden items-center gap-x-2 lg:flex" x-data>
        @if ($isSidebarCollapsibleOnDesktop)
            <x-filament::icon-button color="gray" :icon="$isRtl ? Heroicon::OutlinedChevronLeft : Heroicon::OutlinedChevronRight" :icon-alias="$isRtl
                ? [PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL, PanelsIconAlias::SIDEBAR_EXPAND_BUTTON]
                : PanelsIconAlias::SIDEBAR_EXPAND_BUTTON" icon-size="lg" :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                x-cloak x-on:click="$store.sidebar.open()" x-show="! $store.sidebar.isOpen"
                class="fi-topbar-open-collapse-sidebar-btn !text-white hover:!bg-white/20" />
        @endif

        <x-filament::icon-button color="gray" :icon="$isRtl ? Heroicon::OutlinedChevronRight : Heroicon::OutlinedChevronLeft" :icon-alias="$isRtl
            ? [PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL, PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON]
            : PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON" icon-size="lg" :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
            x-cloak x-on:click="$store.sidebar.close()" x-show="$store.sidebar.isOpen"
            class="fi-topbar-close-collapse-sidebar-btn !text-white hover:!bg-white/20" />
    </div>
@endif
