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
                self::customerForm(),
            ]);
    }

    public static function customerForm(): array
    {
        return [
            Toggle::make('representation')
                ->required()
                ->label('Representación'),
            TextInput::make('full_name')
                ->label('Nombre Completo')
                ->required()
                ->maxLength(255),
            TextInput::make('first_name')
                ->label('Nombres')
                ->maxLength(100),
            TextInput::make('last_name')
                ->label('Apellidos')
                ->maxLength(100),
            TextInput::make('dni')
                ->label('DNI')
                ->numeric()
                ->length(8)
                ->maxLength(8),
            TextInput::make('phone')
                ->label('Teléfono')
                ->tel()
                ->maxLength(20),
            TextInput::make('email')
                ->label('Correo Electrónico')
                ->email()
                ->maxLength(255),
            TextInput::make('address')
                ->label('Dirección')
                ->maxLength(255),
            TextInput::make('ruc')
                ->label('RUC')
                ->numeric()
                ->length(11)
                ->maxLength(11),
            TextInput::make('company')
                ->label('Empresa')
                ->maxLength(255),
        ];
    }
}
