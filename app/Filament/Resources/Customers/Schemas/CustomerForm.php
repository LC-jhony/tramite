<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    /**
     * @return array<int, mixed>
     */
    public static function getComponents(): array
    {
        return [
            Section::make('Datos del cliente')
                ->icon(Heroicon::Identification)
                ->description('Seleccione el tipo de persona e ingrese los datos de identificación.')
                ->schema([
                    ToggleButtons::make('representation')
                        ->label('Tipo de persona')
                        ->boolean(trueLabel: 'Persona Jurídica', falseLabel: 'Persona Natural')
                        ->default(false)
                        ->live()
                        ->required()
                        ->inline()
                        ->fullWidth()
                        ->size(Size::Large)
                        ->colors([
                            1 => 'primary',
                            0 => 'info',
                        ])
                        ->icons([
                            1 => Heroicon::BuildingOffice,
                            0 => Heroicon::User,
                        ]),

                    Fieldset::make('Persona Natural')
                        ->hidden(fn(Get $get): bool => (bool) $get('representation'))
                        ->columns(['default' => 1, 'md' => 2])
                        ->schema([
                            TextInput::make('dni')
                                ->label('DNI')
                                ->placeholder('Ej.: 3917452')
                                ->helperText('Entre 7 y 8 dígitos, sin puntos ni espacios.')
                                ->prefixIcon(Heroicon::Identification)
                                ->type('text')
                                ->inputMode('numeric')
                                ->maxLength(8)
                                ->rule('regex:/^\d{7,8}$/')
                                ->validationMessages([
                                    'regex' => 'El DNI debe tener entre 7 y 8 dígitos.',
                                ])
                                ->required(),

                            TextInput::make('last_name')
                                ->label('Apellidos')
                                ->helperText('Apellido paterno y materno.')
                                ->placeholder('Ej.: Vargas Condori')
                                ->prefixIcon(Heroicon::User)
                                ->maxLength(100)
                                ->required(),

                            TextInput::make('first_name')
                                ->label('Nombres')
                                ->placeholder('Ej.: María Elena')
                                ->prefixIcon(Heroicon::User)
                                ->columnSpan(['default' => 1, 'md' => 2])
                                ->maxLength(255)
                                ->required(),

                            TextInput::make('full_name')
                                ->label('Nombre completo')
                                ->helperText('Nombres y apellidos tal como figuran en el DNI.')
                                ->placeholder('Ej.: María Elena Vargas Condori')
                                ->prefixIcon(Heroicon::Identification)
                                ->columnSpan(['default' => 1, 'md' => 2])
                                ->maxLength(255)
                                ->required(),
                        ]),

                    Fieldset::make('Persona Jurídica')
                        ->hidden(fn(Get $get): bool => ! $get('representation'))
                        ->columns(['default' => 1, 'md' => 2])
                        ->schema([
                            TextInput::make('ruc')
                                ->label('RUC')
                                ->placeholder('Ej.: 1023456027')
                                ->helperText('Exactamente 10 dígitos, sin guiones.')
                                ->prefixIcon(Heroicon::CreditCard)
                                ->type('text')
                                ->inputMode('numeric')
                                ->maxLength(10)
                                ->rule('regex:/^\d{10}$/')
                                ->validationMessages([
                                    'regex' => 'El RUC debe tener exactamente 10 dígitos.',
                                ])
                                ->required(),

                            TextInput::make('company')
                                ->label('Empresa / Razón Social')
                                ->placeholder('Ej.: Constructora Andina S.R.L.')
                                ->prefixIcon(Heroicon::BuildingOffice)
                                ->maxLength(255)
                                ->required(),
                        ]),

                    Fieldset::make('Información de contacto')
                        ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                        ->schema([
                            TextInput::make('phone')
                                ->label('Teléfono')
                                ->placeholder('Ej.: 72581432')
                                ->tel()
                                ->maxLength(20)
                                ->prefixIcon(Heroicon::Phone),

                            TextInput::make('email')
                                ->label('Correo electrónico')
                                ->placeholder('cliente@ejemplo.com')
                                ->email()
                                ->maxLength(255)
                                ->prefixIcon(Heroicon::Envelope),

                            TextInput::make('address')
                                ->label('Dirección')
                                ->placeholder('Ej.: Av. Busch N.º 1245')
                                ->columnSpan(['default' => 1, 'md' => 2, 'xl' => 1])
                                ->maxLength(255)
                                ->prefixIcon(Heroicon::MapPin),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}
