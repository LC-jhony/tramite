<?php

namespace App\Filament\Resources\DocumentTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class DocumentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            Section::make('Información del Tipo de Documento')
                ->icon(Heroicon::DocumentText)
                ->description('Ingrese los datos principales del tipo de documento')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('code')
                        ->label('Código')
                        ->placeholder('Ej: OFICIO, MEMO')
                        ->required()
                        ->maxLength(20)
                        ->columnSpan(1),

                    TextInput::make('name')
                        ->label('Nombre')
                        ->placeholder('Ej: Oficio Multiple')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                    TextInput::make('response_days')
                        ->label('Días de respuesta')
                        ->placeholder('Número de días')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(365)
                        ->suffix('días'),
                ])
                ->collapsible(),

            Section::make('Configuración de Respuesta')
                ->icon(Heroicon::Clock)
                ->description('Configure si este tipo de documento requiere respuesta')
                ->schema([
                    Toggle::make('requires_response')
                        ->label('Requiere respuesta')
                        ->default(true)
                ])
                ->collapsible(),

            Section::make('Estado')
                ->icon(Heroicon::ChartBar)
                ->description('Active o inactive este tipo de documento')
                ->schema([
                    Toggle::make('status')
                        ->label('Estado')
                ])
                ->collapsible(),
        ];
    }
}
