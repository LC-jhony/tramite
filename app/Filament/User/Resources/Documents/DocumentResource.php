<?php

namespace App\Filament\User\Resources\Documents;

use App\Filament\User\Resources\Documents\Pages\CreateDocument;
use App\Filament\User\Resources\Documents\Pages\EditDocument;
use App\Filament\User\Resources\Documents\Pages\ListDocuments;
use App\Filament\User\Resources\Documents\Pages\ViewDocument;
use App\Filament\User\Resources\Documents\RelationManagers\MovementsRelationManager;
use App\Filament\User\Resources\Documents\Schemas\DocumentForm;
use App\Filament\User\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\User\Resources\Documents\Tables\DocumentsTable;
use App\Models\Document;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tapp\FilamentAuditing\RelationManagers\AuditsRelationManager;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'case_number';

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
            AuditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('area_origen_id', auth()->user()->office_id);
    }
}
