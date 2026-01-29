<?php

namespace App\Filament\User\Resources\Documents\Schemas;

use App\Enum\DocumentStatus;
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
                Select::make('document_type_id')
                    ->relationship('documentType', 'name')
                    ->required(),
                Select::make('area_origen_id')
                    ->relationship('areaOrigen', 'name')
                    ->required(),
                Select::make('gestion_id')
                    ->relationship('gestion', 'name')
                    ->required(),
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
                    ->options(DocumentStatus::class)
                    ->required(),
                TextInput::make('priority_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('id_office_destination')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
