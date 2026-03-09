<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'id')
                    ->default(null),
                TextInput::make('document_number')
                    ->required(),
                TextInput::make('case_number')
                    ->required(),
                TextInput::make('subject')
                    ->required(),
                TextInput::make('origen')
                    ->required(),
                TextInput::make('document_type_id')
                    ->required()
                    ->numeric(),
                Select::make('current_office_id')
                    ->relationship('currentOffice', 'name')
                    ->required(),
                TextInput::make('gestion_id')
                    ->required()
                    ->numeric(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(null),
                TextInput::make('folio')
                    ->default(null),
                DatePicker::make('reception_date')
                    ->required(),
                DatePicker::make('response_deadline'),
                TextInput::make('condition')
                    ->default(null),
                Select::make('status')
                    ->options([
            'registrado' => 'Registrado',
            'en_proceso' => 'En proceso',
            'cancelado' => 'Cancelado',
            'finalizado' => 'Finalizado',
            'rechazado' => 'Rechazado',
        ])
                    ->default('registrado')
                    ->required(),
                TextInput::make('priority_id')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
