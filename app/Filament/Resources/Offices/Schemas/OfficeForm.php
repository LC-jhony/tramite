<?php

namespace App\Filament\Resources\Offices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            Section::make('Información de Oficina')
                ->icon(Heroicon::BuildingOffice)
                ->description('Ingrese los datos de la oficina')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label('Código')
                        ->placeholder('Ej: OFI-001')
                        ->required()
                        ->maxLength(20)
                        ->columnSpan(1),

                    TextInput::make('name')
                        ->label('Nombre')
                        ->placeholder('Ej: Oficina de Tramite Documentario')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                ])
                ->collapsible(),

            Section::make('Estado')
                ->icon(Heroicon::ChartBar)
                ->description('Active o inactive esta oficina')
                ->schema([
                    Toggle::make('status')
                        ->label('Estado')
                        ->required(),
                ])
                ->collapsible(),
        ];
    }
}
