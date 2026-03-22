<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            Section::make('Tipo de Persona')
                ->icon(Heroicon::Identification)
                ->description('Seleccione el tipo de cliente que desea registrar')
                ->schema([
                    ToggleButtons::make('representation')
                        ->label('')
                        ->options([
                            'natural' => 'Persona Natural',
                            'juridica' => 'Persona Jurídica',
                        ])
                        ->inline()
                        ->default('natural')
                        ->live()
                        ->required()
                        ->icons([
                            'natural' => Heroicon::User,
                            'juridica' => Heroicon::BuildingOffice,
                        ])
                        ->colors([
                            'natural' => 'info',
                            'juridica' => 'primary',
                        ]),
                ])
                ->collapsible(),

            Section::make('Datos de Persona Natural')
                ->icon(Heroicon::User)
                ->description('Complete la información personal del cliente')
                ->hidden(fn (callable $get): bool => $get('representation') !== 'natural')
                ->columns(3)
                ->schema([
                    TextInput::make('dni')
                        ->label('DNI')
                        ->placeholder('Ingrese su número de DNI')
                        ->numeric()
                        ->length(8)
                        ->maxLength(8)
                        ->required()
                        ->columnSpan(1),

                    TextInput::make('full_name')
                        ->label('Nombre Completo')
                        ->placeholder('Ingrese sus nombres')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('last_name')
                        ->label('Apellido Paterno')
                        ->placeholder('Ingrese su apellido paterno')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('first_name')
                        ->label('Apellido Materno')
                        ->placeholder('Ingrese su apellido materno')
                        ->required()
                        ->maxLength(100),
                ])
                ->collapsible(),

            Section::make('Datos de Persona Jurídica')
                ->icon(Heroicon::BuildingOffice)
                ->description('Complete la información de la empresa')
                ->hidden(fn (callable $get): bool => $get('representation') !== 'juridica')
                ->columns(2)
                ->schema([
                    TextInput::make('ruc')
                        ->label('RUC')
                        ->placeholder('Ingrese el número de RUC')
                        ->numeric()
                        ->length(11)
                        ->maxLength(11)
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('company')
                        ->label('Empresa / Razón Social')
                        ->placeholder('Ingrese el nombre de la empresa')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            Section::make('Información de Contacto')
                ->icon(Heroicon::PhoneArrowUpRight)
                ->description('Datos para contactar al cliente')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->placeholder('999 999 999')
                        ->tel()
                        ->maxLength(20)
                        ->prefixIcon(Heroicon::Phone),

                    TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->placeholder('cliente@ejemplo.com')
                        ->email()
                        ->maxLength(255)
                        ->prefixIcon(Heroicon::Envelope),

                    TextInput::make('address')
                        ->label('Dirección')
                        ->placeholder('Av. Principal 123')
                        ->maxLength(255)
                        ->prefixIcon(Heroicon::MapPin),
                ])
                ->collapsible(),
        ];
    }
}
