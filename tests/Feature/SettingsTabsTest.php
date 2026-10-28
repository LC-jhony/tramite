<?php

use App\Filament\Settings\SettingsSchemaRegistry;

test('los esquemas se agrupan por pestaña respetando el sort', function () {
    $tabs = app(SettingsSchemaRegistry::class)->byTab();

    expect($tabs->keys()->all())
        ->toBe(['general', 'social', 'legal', 'theme'])
        ->and($tabs->get('general')->map(fn ($settingsSchema) => $settingsSchema->group())->all())
        ->toBe(['general']);
});

test('cada esquema ocupa su propia pestaña', function () {
    $registry = app(SettingsSchemaRegistry::class);

    expect($registry->find('social')->tab())->toBe('social')
        ->and($registry->find('general')->tab())->toBe('general')
        ->and($registry->find('legal')->tab())->toBe('legal')
        ->and($registry->find('theme')->tab())->toBe('theme');
});

test('seo y mantenimiento no están registrados como pestañas', function () {
    $registry = app(SettingsSchemaRegistry::class);

    expect($registry->groups())
        ->not->toContain('seo', 'maintenance')
        ->and($registry->find('seo'))->toBeNull()
        ->and($registry->find('maintenance'))->toBeNull();
});
