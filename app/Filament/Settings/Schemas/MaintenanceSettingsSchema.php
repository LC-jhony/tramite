<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class MaintenanceSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 50;

    protected int $columns = 1;

    public function group(): string
    {
        return 'maintenance';
    }

    public function label(): string
    {
        return 'Mantenimiento';
    }

    public function icon(): string
    {
        return 'heroicon-o-wrench-screwdriver';
    }

    public function schema(): array
    {
        return [
            Toggle::make('enabled')
                ->label('Modo mantenimiento activo')
                ->live()
                ->helperText('Al activarlo, los visitantes verán el mensaje de abajo en lugar del sitio.'),

            Textarea::make('message')
                ->label('Mensaje a mostrar a los visitantes')
                ->rows(2)
                ->default('Estamos en mantenimiento. Vuelve pronto.'),

            TagsInput::make('allowed_ips')
                ->label('IPs permitidas durante el mantenimiento')
                ->helperText('Estas IPs seguirán viendo el sitio normalmente.')
                ->placeholder('Escribe una IP y presiona Enter'),
        ];
    }
}
