<?php

namespace App\Filament\Resources\Administrations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdministrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('start_period')
                    ->label('Periodo Inicio')
                    ->required()
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
                    }),
                TextInput::make('end_period')
                    ->label('Periodo Fin')
                    ->required()
                    ->numeric()
                    ->minLength(4)
                    ->maxLength(4)
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('mayor')
                    ->label('Alcalde')
                    ->required(),
                Toggle::make('status')
                    ->label('Estado')
                    ->required()
                    ->default(false),
            ]);
    }
}
