<?php

namespace App\Filament\Settings\Schemas;

use App\Filament\Settings\ThemeColorPalette;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\View;

/**
 * Sección "Tema del panel": elige el color primario de todo el panel.
 *
 * El valor se guarda en el grupo `theme` de la tabla `settings` (una fila,
 * `name='config'`) y lo consume `AdminPanelProvider` en su `->colors()`,
 * que Filament evalúa en cada petición del panel.
 */
class ColorThemeSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 60;

    protected int $columns = 1;

    public function group(): string
    {
        return 'theme';
    }

    public function label(): string
    {
        return 'Tema del panel';
    }

    public function icon(): string
    {
        return 'heroicon-o-paint-brush';
    }

    public function schema(): array
    {
        return [
            Select::make('primary')
                ->label('Color primario')
                ->options(ThemeColorPalette::options())
                ->default(ThemeColorPalette::DEFAULT)
                ->required()
                ->live()
                ->native(false)
                ->searchable()
                ->helperText('Se aplica a botones, enlaces, sidebar y cualquier elemento activo de todo el panel. Se usa al guardar.'),

            // Rejilla de paletas con su nombre: sirve de vista previa y de
            // selector alternativo. No es un `Field`, así que no aporta
            // estado al formulario y `ManageSettings::save()` no lo ve.
            View::make('filament.settings.partials.theme-color-swatches')
                ->columnSpanFull(),
        ];
    }
}
