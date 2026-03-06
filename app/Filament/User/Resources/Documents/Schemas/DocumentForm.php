<?php

namespace App\Filament\User\Resources\Documents\Schemas;

use App\Enum\DocumentStatus;
use App\Models\Administration;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
                    ->required()
                    ->default('Interno')
                    ->disabled()
                    ->dehydrated(),
                Select::make('document_type_id')
                    ->options(DocumentType::where('status', true)->pluck('name', 'id'))
                    ->required()
                    ->default(false),
                Select::make('current_office_id')
                    ->options(Office::where('status', true)->pluck('name', 'id'))
                    ->default(Auth::user()->office_id)
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                Select::make('gestion_id')
                    ->options(Administration::where('status', true)->pluck('name', 'id'))
                    ->default(function () {
                        return Administration::where('status', true)->value('id');
                    })
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                Select::make('user_id')
                    ->options(User::all()->pluck('name', 'id'))
                    ->default(Auth::id())
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('folio')
                    ->required()
                    ->numeric(),
                DatePicker::make('reception_date')
                    ->default(now())
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                DatePicker::make('response_deadline'),
                ToggleButtons::make('condition')
                    ->boolean()
                    ->default(true),
                Select::make('status')
                    ->options(DocumentStatus::class)
                    ->default(DocumentStatus::Registrado->value)
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('priority_id')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
