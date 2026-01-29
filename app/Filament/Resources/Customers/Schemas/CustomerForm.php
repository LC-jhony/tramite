<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('representation')
                    ->required(),
                TextInput::make('full_name')
                    ->default(null),
                TextInput::make('first_name')
                    ->default(null),
                TextInput::make('last_name')
                    ->default(null),
                TextInput::make('dni')
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('address')
                    ->default(null),
                TextInput::make('ruc')
                    ->default(null),
                TextInput::make('company')
                    ->default(null),
            ]);
    }
}
