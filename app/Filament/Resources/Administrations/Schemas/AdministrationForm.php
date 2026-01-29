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
                    ->required(),
                TextInput::make('start_period')
                    ->required(),
                TextInput::make('end_period')
                    ->required(),
                TextInput::make('mayor')
                    ->required(),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
