<div 
    class="fi-topbar-ctn fixed top-0 left-0 right-0 bg-primary-600 dark:bg-primary-700 transition-colors z-10 overflow-x-hidden"
    x-data="{ 
        height: 60,
        isMobile: window.innerWidth < 1024,
        init() {
            this.updateHeight();
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 1024;
                this.updateHeight();
            });
        },
        updateHeight() {
            const height = this.isMobile ? 60 : 60;
            this.height = height;
            document.body.style.paddingTop = height + 'px';
        }
    }"
    x-init="init()"
    x-on:destroy.window="document.body.classList.remove('pt-[60px]'); document.body.style.paddingTop = ''"
>
    
    @php
        use Filament\Facades\Filament;
        use Filament\Support\Facades\FilamentView;
        use Filament\View\PanelsRenderHook;
        use Filament\Support\Icons\Heroicon;
        use Filament\View\PanelsIconAlias;
        use function Filament\Support\prepare_inherited_attributes;
        use function Filament\Support\generate_href_html;
        
        $navigation = filament()->getNavigation();
        $hasTopNav = filament()->hasTopNavigation();
        $hasNavigation = filament()->hasNavigation();
        $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
        $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
        // Fix for undefined $isRtl
        $isRtl = false; 
    @endphp

    <nav class="fi-topbar mx-auto w-full bg-primary-600 dark:bg-primary-700 z-50 relative overflow-x-hidden flex items-center justify-between h-14 lg:h-15">
        <div class="flex items-center">
            {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_START) }}
            
            @if ($hasNavigation)
                <x-filament::icon-button
                    color="gray"
                    :icon="Heroicon::OutlinedBars3"
                    :icon-alias="PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON"
                    icon-size="lg"
                    :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                    x-cloak
                    x-data="{}"
                    x-on:click="$store.sidebar.open()"
                    x-show="! $store.sidebar.isOpen"
                    class="fi-topbar-open-sidebar-btn lg:hidden"
                />

                <x-filament::icon-button
                    color="gray"
                    :icon="Heroicon::OutlinedXMark"
                    :icon-alias="PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON"
                    icon-size="lg"
                    :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                    x-cloak
                    x-data="{}"
                    x-on:click="$store.sidebar.close()"
                    x-show="$store.sidebar.isOpen"
                    class="fi-topbar-close-sidebar-btn lg:hidden"
                />
            @endif
            
            <div class="fi-topbar-start flex items-center">
                {{-- Logo and collapsible sidebar buttons --}}
                @if ($isSidebarCollapsibleOnDesktop)
                    <x-filament::icon-button
                        color="gray"
                        :icon="$isRtl ? Heroicon::OutlinedChevronLeft : Heroicon::OutlinedChevronRight"
                        :icon-alias="
                            $isRtl
                            ? [
                                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL,
                                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON,
                            ]
                            : PanelsIconAlias::SIDEBAR_EXPAND_BUTTON
                        "
                        icon-size="lg"
                        :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                        x-cloak
                        x-data="{}"
                        x-on:click="$store.sidebar.open()"
                        x-show="! $store.sidebar.isOpen"
                        class="fi-topbar-open-collapse-sidebar-btn hidden lg:block"
                    />
                @endif

                @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
                    <x-filament::icon-button
                        color="gray"
                        :icon="$isRtl ? Heroicon::OutlinedChevronRight : Heroicon::OutlinedChevronLeft"
                        :icon-alias="
                            $isRtl
                            ? [
                                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL,
                                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON,
                            ]
                            : PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON
                        "
                        icon-size="lg"
                        :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                        x-cloak
                        x-data="{}"
                        x-on:click="$store.sidebar.close()"
                        x-show="$store.sidebar.isOpen"
                        class="fi-topbar-close-collapse-sidebar-btn hidden lg:block"
                    />
                @endif

                {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_LOGO_BEFORE) }}

                @if ($homeUrl = filament()->getHomeUrl())
                    <a {{ generate_href_html($homeUrl) }} class="flex items-center">
                        <x-filament-panels::logo class="h-8 w-auto"/>
                    </a>
                @else
                    <x-filament-panels::logo class="h-8 w-auto"/>
                @endif

                {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_LOGO_AFTER) }}
            </div>
        </div>
      
        <ul class="fi-topbar-nav-groups hidden lg:flex items-center space-x-1 overflow-x-auto scrollbar-hide [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                @foreach ($navigation as $group)
                    @php
                        $groupLabel = $group->getLabel();
                        $groupExtraTopbarAttributeBag = $group->getExtraTopbarAttributeBag();
                        $isGroupActive = $group->isActive();
                        $groupIcon = $group->getIcon();
                    @endphp

                    @if ($groupLabel)
                        <x-filament::dropdown
                            placement="bottom-start"
                            teleport
                            :attributes="\Filament\Support\prepare_inherited_attributes($groupExtraTopbarAttributeBag)"
                        >
                            <x-slot name="trigger">
                                <x-filament-panels::topbar.item
                                    :active="$isGroupActive"
                                    :icon="$groupIcon"
                                >
                                    {{ $groupLabel }}
                                </x-filament-panels::topbar.item>
                            </x-slot>

                            @foreach ($group->getItems() as $item)
                                <x-filament::dropdown.list.item
                                    :color="$item->isActive() ? 'primary' : 'gray'"
                                    :href="$item->getUrl()"
                                    :icon="$item->getIcon()"
                                    :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null"
                                >
                                    {{ $item->getLabel() }}
                                </x-filament::dropdown.list.item>
                            @endforeach
                        </x-filament::dropdown>
                    @else
                        @foreach ($group->getItems() as $item)
                            <x-filament-panels::topbar.item
                                :active="$item->isActive()"
                                :active-icon="$item->getActiveIcon()"
                                :badge="$item->getBadge()"
                                :badge-color="$item->getBadgeColor()"
                                :icon="$item->getIcon()"
                                :should-open-url-in-new-tab="$item->shouldOpenUrlInNewTab()"
                                :url="$item->getUrl()"
                            >
                                {{ $item->getLabel() }}
                            </x-filament-panels::topbar.item>
                        @endforeach
                    @endif
                @endforeach
            </ul>

        <div class="fi-topbar-end flex items-center space-x-2" x-persist="topbar.end.panel-{{ filament()->getId() }}">
            {{ FilamentView::renderHook(PanelsRenderHook::GLOBAL_SEARCH_BEFORE) }}

            @if (filament()->isGlobalSearchEnabled())
                 @livewire(\Filament\Livewire\GlobalSearch::class)
            @endif

            {{ FilamentView::renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER) }}

            @if (filament()->auth()->check())
                @if (filament()->hasDatabaseNotifications())
                    @livewire(DatabaseNotifications::class, [
                         'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                     ])
                @endif

                @if (filament()->hasUserMenu())
                    <x-filament-panels::user-menu/>
                @endif
            @endif
        </div>

        {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_END) }}
    </nav>
</div>
