<?php

namespace App\Filament\Resources\Administrations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AdministrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            Section::make('Periodo de Gestión')
                ->icon(Heroicon::Calendar)
                ->description('Ingrese el período de la gestión municipal')
                ->schema([
                    TextInput::make('start_period')
                        ->label('Año Inicio')
                        ->placeholder('Ej: 2023')
                        ->numeric()
                        ->minLength(4)
                        ->maxLength(4)
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state && is_numeric($state)) {
                                $endYear = (int) $state + 3;
                                $set('end_period', (string) $endYear);
                                $set('name', 'Gestión Municipal: '.$state.' - '.$endYear);
                            }
                        })
                        ->columnSpan(1),

                    TextInput::make('end_period')
                        ->label('Año Fin')
                        ->placeholder('Se calcula automáticamente')
                        ->numeric()
                        ->minLength(4)
                        ->maxLength(4)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),
                ])
                ->collapsible()
                ->columns(2),

            Section::make('Datos del Alcaldía')
                ->icon(Heroicon::BuildingOffice)
                ->description('Información del alcalde o autoridad')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre de Gestión')
                        ->placeholder('Se genera automáticamente')
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('mayor')
                        ->label('Alcalde / Autoridad')
                        ->placeholder('Nombre del alcalde')
                        ->required()
                        ->prefixIcon(Heroicon::User),
                ])
                ->collapsible(),

            Section::make('Estado')
                ->icon(Heroicon::ChartBar)
                ->description('Active o inactive esta gestión')
                ->schema([
                    ToggleButtons::make('status')
                        ->label('Estado')
                        ->boolean()
                        ->inline()
                        ->required()
                        ->default(false)
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
