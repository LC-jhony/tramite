@props([
    'item',
    'tag' => 'a',
    'submenuTrigger' => false,
    // 'dropdown': item inside a dropdown panel (white background,
    //             uses x-filament::dropdown.list.item). Default behavior.
    // 'pill':     top-level navigation button on the colored header
    //             (same look as the nav-dropdown trigger).
    'variant' => 'dropdown',
    // When true, an item is marked active if any of its child items are
    // active (isChildItemsActive()). Dropdown mode always needs this;
    // pill mode is opt-in because it only checked isActive().
    'includeChildActiveState' => true,
])

@php
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Arr;
    $isItemActive = $includeChildActiveState ? $item->isChildItemsActive() || $item->isActive() : $item->isActive();
    $itemBadge = $item->getBadge();
    $itemBadgeColor = $item->getBadgeColor($itemBadge);
    $itemBadgeTooltip = $item->getBadgeTooltip($itemBadge);
    $itemUrl = $item->getUrl();
    $itemIcon = $isItemActive ? $item->getActiveIcon() ?? $item->getIcon() : $item->getIcon();
    $shouldItemOpenUrlInNewTab = $item->shouldOpenUrlInNewTab();
    $itemExtraAttributes = $item->getExtraAttributeBag();
@endphp

@if ($variant === 'pill')
    @php
        $pillClasses = Arr::toCssClasses([
            'rounded-lg px-4 py-1.5',
            'bg-primary-900/40' => $isItemActive,
            'transition hover:bg-white/10' => !$isItemActive,
        ]);
    @endphp

    <a href="{{ $itemUrl }}" @if ($shouldItemOpenUrlInNewTab) target="_blank" @endif
        @if ($isItemActive) aria-current="page" @endif {{ $itemExtraAttributes }}
        {{ $attributes->merge(['class' => $pillClasses]) }}>
        @if ($itemIcon)
            <x-filament::icon :icon="$itemIcon" class="mr-1 inline h-4 w-4" />
        @endif
        {{ $item->getLabel() }}
        @if (filled($itemBadge))
            <x-filament::badge :color="$itemBadgeColor" size="sm" :tooltip="$itemBadgeTooltip" class="ml-1">
                {{ $itemBadge }}
            </x-filament::badge>
        @endif
    </a>
@else
    <x-filament::dropdown.list.item :tag="$tag" :color="$isItemActive ? 'primary' : 'gray'" :href="$itemUrl" :icon="$itemIcon"
        :badge="$itemBadge" :badge-color="$itemBadgeColor" :badge-tooltip="$itemBadgeTooltip" :target="$shouldItemOpenUrlInNewTab ? '_blank' : null" :aria-current="$isItemActive ? 'page' : null" :attributes="\Filament\Support\prepare_inherited_attributes($itemExtraAttributes)"
        {{ $attributes }}>
        @if ($submenuTrigger)
            <span class="flex items-center gap-1">
                {{ $item->getLabel() }}
                <x-filament::icon :icon="Heroicon::ChevronRight" class="ml-1 size-3 opacity-50" />
            </span>
        @else
            {{ $item->getLabel() }}
        @endif
    </x-filament::dropdown.list.item>
@endif
