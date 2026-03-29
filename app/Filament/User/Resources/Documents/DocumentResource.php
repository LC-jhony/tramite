<?php

namespace App\Filament\User\Resources\Documents;

use App\Filament\User\Resources\Documents\Pages\CreateDocument;
use App\Filament\User\Resources\Documents\Pages\EditDocument;
use App\Filament\User\Resources\Documents\Pages\ListDocuments;
use App\Filament\User\Resources\Documents\Schemas\DocumentForm;
use App\Filament\User\Resources\Documents\Tables\DocumentsTable;
use App\Models\Document;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'document_number';

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            // ->where('user_id', auth()->id())
            ->where('current_office_id', auth()->user()?->office_id);
    }
}
