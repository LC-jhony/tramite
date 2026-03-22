<?php

namespace App\Filament\Resources\Priorities\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PriorityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                ColorPicker::make('color')
                    ->required()
                    ->default('gray'),
                TextInput::make('days')
                    ->required()
                    ->numeric()
                    ->default(5),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
