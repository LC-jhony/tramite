<?php

namespace App\Providers\Filament;

use App\Filament\User\Widgets\DocumentWidget;
use App\Filament\User\Widgets\MovementsByActionWidget;
use App\Filament\User\Widgets\MovementWidget;
use App\Filament\User\Widgets\RecentDocumentsWidget;
use App\Filament\User\Widgets\UserStatsWidget;
use App\Livewire\CustomTopNavigation;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CrescentPurchasing\FilamentAuditing\FilamentAuditingPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->brandName(fn () => auth()->user()?->office?->name ?? config('app.name'))
            ->id('user')
            ->path('user')
            ->registration()
            ->emailVerification()
            // ->topbarLivewireComponent(CustomTopNavigation::class)
            ->topNavigation()
            ->viteTheme('resources/css/filament/user/theme.css')
            ->login()
            ->colors([
                'primary' => Color::hex('#008255'),
            ])
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\Filament\User\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\Filament\User\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                DocumentWidget::class,
                MovementWidget::class,
                MovementsByActionWidget::class,
                UserStatsWidget::class,
                RecentDocumentsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,

            ])
            ->plugins([
                // FilamentSpatieLaravelBackupPlugin::make(),
                FilamentShieldPlugin::make(),
                FilamentAuditingPlugin::make()
                    ->formatAuditableTypeUsing(fn (string $value): string => strtoupper($value))
                    ->navigationGroup('Settings')
                    ->navigationIcon('hugeicons-audit-02'),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
