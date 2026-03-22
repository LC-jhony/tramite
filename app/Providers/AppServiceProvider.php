<?php

namespace App\Providers;

use BezhanSalleh\PanelSwitch\PanelSwitch;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentColor::register([
            'primary' => Color::hex('#004f3b'),
        ]);

        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->panels(['admin', 'user'])
                ->sort()
                ->slideOver()
                ->labels([
                    'admin' => 'Administración',
                    'user' => 'Trámites',
                ])
                ->icons([
                    'admin' => 'heroicon-o-cog-6-tooth',
                    'user' => 'heroicon-o-document-duplicate',
                ])
                ->iconSize(20)
                ->modalHeading('Cambiar de Panel')
                ->modalWidth('sm');
        });
    }
}
