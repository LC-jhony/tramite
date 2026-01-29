<?php

namespace App\Filament\Resources\Offices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('parent_office_id')
                    ->relationship('parentOffice', 'name')
                    ->default(null),
                TextInput::make('level')
                    ->required()
                    ->numeric(),
                TextInput::make('manager')
                    ->default(null),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
