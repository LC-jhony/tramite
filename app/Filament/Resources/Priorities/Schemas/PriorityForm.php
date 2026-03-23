<?php

namespace App\Filament\Resources\Priorities\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PriorityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            Section::make('Información de Prioridad')
                ->icon(Heroicon::Flag)
                ->description('Ingrese los datos de la prioridad')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->placeholder('Ej: Alta, Media, Baja')
                        ->required()
                        ->columnSpan(1),

                    TextInput::make('days')
                        ->label('Días de atención')
                        ->placeholder('Número de días')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(365)
                        ->suffix('días')
                        ->columnSpan(1),
                ])
                ->collapsible(),

            Section::make('Color de Prioridad')
                ->icon(Heroicon::Swatch)
                ->description('Seleccione un color para identificar esta prioridad')
                ->schema([
                    ColorPicker::make('color')
                        ->label('Color')
                        ->required()
                        ->default('gray'),
                ])
                ->collapsible(),

            Section::make('Estado')
                ->icon(Heroicon::ChartBar)
                ->description('Active o inactive esta prioridad')
                ->schema([
                    ToggleButtons::make('status')
                        ->label('Estado')
                        ->boolean()
                        ->inline()
                        ->required()
                        ->icons([
                            true => Heroicon::CheckCircle,
                            false => Heroicon::XCircle,
                        ])
                        ->colors([
                            true => 'success',
                            false => 'danger',
                        ]),
                ])
                ->collapsible(),
        ];
    }
}
