<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Office;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Usuario')
                    ->icon(Heroicon::UserCircle)
                    ->description('Ingrese los datos del usuario')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->placeholder('Nombre completo')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->placeholder('correo@ejemplo.com')
                            ->email()
                            ->required()
                            ->columnSpan(1),

                        Select::make('roles')
                            ->label('Rol')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpan(1),

                        Select::make('office_id')
                            ->label('Oficina')
                            ->options(Office::where('status', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->collapsible(),

                Section::make('Contraseña')
                    ->icon(Heroicon::Key)
                    ->description('Establecer contraseña del usuario')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->columnSpan(1),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->same('password')
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->columnSpan(1),
                    ])
                    ->collapsible()
                    ->visible(fn (string $operation): bool => $operation === 'create'),

                Section::make('Actualizar Contraseña')
                    ->icon(Heroicon::Key)
                    ->description('Cambiar contraseña (solo edición)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Nueva Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->columnSpan(1),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->same('password')
                            ->columnSpan(1),
                    ])
                    ->collapsible()
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }
}
