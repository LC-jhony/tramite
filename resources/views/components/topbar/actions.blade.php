@php
    use Filament\Livewire\DatabaseNotifications;
    use Filament\Livewire\GlobalSearch;
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
@endphp

<div class="flex items-center space-x-4">
    {{ FilamentView::renderHook(PanelsRenderHook::GLOBAL_SEARCH_BEFORE) }}

    {{-- Global Search --}}
    @if (filament()->isGlobalSearchEnabled())
        @livewire(GlobalSearch::class)
    @endif

    {{ FilamentView::renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER) }}

    {{-- Database Notifications --}}
    @if (filament()->auth()->check())
        @if (filament()->hasDatabaseNotifications())
            @livewire(DatabaseNotifications::class, [
                'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
            ])
        @endif

        {{-- User Menu --}}
        @if (filament()->hasUserMenu())
            <x-filament-panels::user-menu />
        @endif
    @endif
</div>
