<?php

namespace App\Providers\Filament;

use App\Filament\Settings\ThemeColorPalette;
use Filament\Enums\GlobalSearchPosition;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JohnRivera7\FilamentMia\MiaTheme;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->colors(fn (): array => [
                'primary' => $this->primaryColorPalette(),
            ])
            // Identidad del panel, tomada de la pestaña "General" (grupo
            // `general` de la tabla `settings`): nombre, logo claro, logo en
            // modo oscuro, favicon y el destino del enlace del logo.
            //
            // Van como closures por el MISMO motivo que ->colors(): Filament
            // las guarda en el Panel y las evalúa al renderizar (getBrandName,
            // getBrandLogo, getDarkModeBrandLogo, getFavicon, getHomeUrl),
            // nunca durante el boot. Así el sitio público y los `php artisan`
            // no tocan la tabla `settings`. Ver §19 de la guía.
            ->brandName(fn (): ?string => $this->settingValue('general.site_name'))
            ->brandLogo(fn (): ?string => $this->generalAssetUrl('logo'))
            ->darkModeBrandLogo(fn (): ?string => $this->generalAssetUrl('logo_dark'))
            ->favicon(fn (): ?string => $this->generalAssetUrl('favicon'))
            ->homeUrl(fn (): ?string => $this->settingValue('general.site_url'))
            ->globalSearch(position: GlobalSearchPosition::Sidebar)
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
        // ->plugins([
        //     MiaTheme::make()
        //         ->accentColor('#C9A227')
        //         ->secondaryColor('#E8C4C0')
        //         ->font('Jost', 'Cormorant Garamond')
        //         ->roundness('soft')
        //         ->density('comfortable')
        //         ->elevation(0.75)
        //         ->motion()
        //         ->darkMode(),
        // ]);
    }

    /**
     * Paleta del color primario del panel, tomada de la sección
     * "Tema del panel" (grupo `theme` de la tabla `settings`).
     *
     * Se resuelve dentro de la closure que recibe `->colors()` porque ahí es
     * donde Filament registra los colores: en `Panel::boot()`, que se ejecuta
     * en el middleware `SetUpPanel` de cada petición del panel. Así el sitio
     * público y los comandos de consola no tocan la tabla `settings`.
     *
     * @return array<int, string>
     */
    protected function primaryColorPalette(): array
    {
        try {
            $name = setting('theme.primary', ThemeColorPalette::DEFAULT);
        } catch (\Throwable) {
            // Si la tabla `settings` todavía no existe (primer arranque,
            // migraciones a medias) el panel no debe caerse por un color.
            $name = ThemeColorPalette::DEFAULT;
        }

        return ThemeColorPalette::palette(is_string($name) ? $name : null);
    }

    /**
     * Valor de texto de una setting, tolerante a una tabla que todavía no
     * existe (primer arranque, migraciones a medias) y a valores vacíos.
     *
     * Devuelve `null` cuando no hay valor: Filament trata `null` como
     * "no configurado" y cae a su default (`config('app.name')` en
     * `brandName()`, `getUrl()` en `homeUrl()`).
     */
    protected function settingValue(string $key): ?string
    {
        try {
            $value = setting($key);
        } catch (\Throwable) {
            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }

    /**
     * URL pública de un archivo subido desde la pestaña "General":
     * `logo`, `logo_dark` o `favicon`.
     *
     * Devuelve `null` si la ruta no está en `settings/` o si el archivo no
     * existe en el disco: así, sin logo, la marca del panel se ve con el
     * nombre en texto en lugar de una imagen rota (misma regla que §19.3).
     *
     * Usa `asset()` y no `Storage::url()`: la URL nace del origen de la
     * página en curso, así carga igual en `localhost`, `127.0.0.1`, otro
     * puerto o `https`. `Storage::url()` devuelve `APP_URL.'/storage/…'`
     * (origen fijo), que es lo que el navegador bloquea por CORS cuando el
     * panel se abre desde otro host (ver §19.4).
     */
    protected function generalAssetUrl(string $field): ?string
    {
        $path = $this->settingValue('general.'.$field);

        if ($path === null || ! str_starts_with($path, 'settings/')) {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/'.$path);
    }
}
