@props(['group'])

@php
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Arr;
    $groupLabel = $group->getLabel();
    $groupIcon = $group->getIcon();
    $isGroupActive = $group->isActive();
    $groupExtraTopbarAttributeBag = $group->getExtraTopbarAttributeBag();
    // Partition group items into "lists" separated by items with children,
    // to render dividers between sections in the dropdown panel.
    $lists = [];
    foreach ($group->getItems() as $item) {
        if ($item->getChildItems()) {
            $lists[] = [$item];
            $lists[] = [];
            continue;
        }
        if (empty($lists)) {
            $lists[] = [$item];
            continue;
        }
        $lists[count($lists) - 1][] = $item;
    }
    if (!empty($lists) && empty($lists[count($lists) - 1])) {
        array_pop($lists);
    }
    $triggerClasses = Arr::toCssClasses([
        'rounded-lg px-4 py-1.5',
        'bg-primary-900/40' => $isGroupActive,
        'transition hover:bg-white/10' => !$isGroupActive,
    ]);
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false" @keydown.escape="open = false"
    @if ($groupExtraTopbarAttributeBag) {{ $groupExtraTopbarAttributeBag }} @endif>
    <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="true" class="{{ $triggerClasses }}">
        @if ($groupIcon)
            <x-filament::icon :icon="$groupIcon" class="mr-1 inline h-4 w-4" />
        @endif
        {{ $groupLabel }}
        <x-filament::icon :icon="Heroicon::ChevronDown" class="ml-1 inline h-3 w-3 opacity-60" />
    </button>

    <div x-show="open" x-cloak x-transition role="menu"
        class="fi-topbar-dropdown-panel absolute left-0 z-50 mt-2 max-w-[calc(100vw-2rem)] w-60 rounded-lg bg-white py-1 shadow-lg dark:bg-gray-800">
        @foreach ($lists as $list)
            @if (empty($list))
                <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
            @else
                @foreach ($list as $item)
                    @php
                        $itemChildItems = $item->getChildItems();
                        $itemHasChildren = filled($itemChildItems);
                    @endphp

                    @if ($itemHasChildren)
                        <div class="fi-topbar-dropdown-item-with-submenu relative" x-data="{
                            submenuOpen: false,
                            submenuDirection: 'right',
                            initSubmenu() {
                                this.$nextTick(() => {
                                    const trigger = this.$refs.submenuTrigger;
                                    const panel = this.$refs.submenuPanel;
                                    if (trigger && panel) {
                                        const triggerRect = trigger.getBoundingClientRect();
                                        const spaceRight = window.innerWidth - triggerRect.right;
                                        if (spaceRight < panel.offsetWidth + 8) {
                                            this.submenuDirection = 'left';
                                        }
                                    }
                                });
                            }
                        }"
                            @mouseenter="submenuOpen = true; initSubmenu()" @mouseleave="submenuOpen = false"
                            @keydown.escape="submenuOpen = false">
                            <x-topbar.navigation-item :item="$item" tag="a" :submenu-trigger="true"
                                x-ref="submenuTrigger" />

                            <div x-show="submenuOpen" x-cloak x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="fi-topbar-submenu absolute top-0 z-50 ml-1 w-56 rounded-lg bg-white py-1 shadow-lg dark:bg-gray-800"
                                :class="submenuDirection === 'left' ? 'right-full mr-1' :
                                    'left-full'"
                                x-ref="submenuPanel" @mouseenter="submenuOpen = true" @mouseleave="submenuOpen = false">
                                @foreach ($itemChildItems as $childItem)
                                    <x-topbar.navigation-item :item="$childItem" />
                                @endforeach
                            </div>
                        </div>
                    @else
                        <x-topbar.navigation-item :item="$item" />
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>
</div>
