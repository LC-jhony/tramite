<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Office;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([
                Section::make('Registrar usuario')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        TextInput::make('email')
                            ->label('Correo electronico')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn($state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->visible(fn(string $operation): bool => $operation === 'create'),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->same('password')
                            ->visible(fn(string $operation): bool => $operation === 'create'),
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Select::make('office_id')
                            ->label('Oficina')
                            ->options(Office::where('status', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->native(false),
                    ]),

                Fieldset::make('Contraseña')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn($state): bool => filled($state)),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->same('password'),
                    ])
                    ->visible(fn(string $operation): bool => $operation === 'edit'),
            ]);
    }
}
