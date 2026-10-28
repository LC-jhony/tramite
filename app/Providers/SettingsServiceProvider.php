<?php

namespace App\Providers;

use App\Filament\Settings\SettingsSchemaRegistry;
use App\Settings\Contracts\SettingsRepository;
use App\Settings\EloquentSettingsRepository;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/settings.php', 'settings');

        $this->app->singleton(SettingsRepository::class, function (): EloquentSettingsRepository {
            return new EloquentSettingsRepository(
                settingName: (string) config('settings.setting_name', 'config'),
                cacheEnabled: (bool) config('settings.cache.enabled', true),
                cacheStore: config('settings.cache.store'),
                cachePrefix: (string) config('settings.cache.prefix', 'settings'),
            );
        });

        $this->app->singleton(SettingsSchemaRegistry::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/settings.php' => config_path('settings.php'),
        ], 'settings-config');
    }
}
