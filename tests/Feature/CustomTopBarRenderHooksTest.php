<?php

use App\Livewire\Custom\CustomTopBar;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('user'));

    FilamentView::registerRenderHook(
        PanelsRenderHook::TOPBAR_START,
        fn (): string => '<!--marker:topbar-start-->',
    );

    FilamentView::registerRenderHook(
        PanelsRenderHook::TOPBAR_LOGO_BEFORE,
        fn (): string => '<!--marker:logo-before-->',
    );

    FilamentView::registerRenderHook(
        PanelsRenderHook::TOPBAR_LOGO_AFTER,
        fn (): string => '<!--marker:logo-after-->',
    );

    FilamentView::registerRenderHook(
        PanelsRenderHook::TOPBAR_END,
        fn (): string => '<!--marker:topbar-end-->',
    );
});

test('el topbar propio renderiza los hooks de Filament en la misma posición que el nativo', function () {
    $component = Livewire::test(CustomTopBar::class);

    $component
        ->assertSee('<!--marker:topbar-start-->', false)
        ->assertSee('<!--marker:logo-before-->', false)
        ->assertSee('<!--marker:logo-after-->', false)
        ->assertSee('<!--marker:topbar-end-->', false);

    $html = $component->html();
    $offsetOf = fn (string $marker): int => strpos($html, $marker);

    expect($offsetOf('<!--marker:topbar-start-->'))
        ->toBeLessThan($offsetOf('<!--marker:logo-before-->'))
        ->and($offsetOf('<!--marker:logo-before-->'))->toBeLessThan($offsetOf('<!--marker:logo-after-->'))
        ->and($offsetOf('<!--marker:logo-after-->'))->toBeLessThan($offsetOf('<!--marker:topbar-end-->'));
});
