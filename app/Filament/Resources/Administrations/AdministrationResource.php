<?php

namespace App\Filament\Resources\Administrations;

use App\Filament\Resources\Administrations\Pages\CreateAdministration;
use App\Filament\Resources\Administrations\Pages\EditAdministration;
use App\Filament\Resources\Administrations\Pages\ListAdministrations;
use App\Filament\Resources\Administrations\Schemas\AdministrationForm;
use App\Filament\Resources\Administrations\Tables\AdministrationsTable;
use App\Models\Administration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdministrationResource extends Resource
{
    protected static ?string $model = Administration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdministrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdministrationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdministrations::route('/'),
            'create' => CreateAdministration::route('/create'),
            'edit' => EditAdministration::route('/{record}/edit'),
        ];
    }
}
