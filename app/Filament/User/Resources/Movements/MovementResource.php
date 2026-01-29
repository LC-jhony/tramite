<?php

namespace App\Filament\User\Resources\Movements;

use App\Filament\User\Resources\Movements\Pages\CreateMovement;
use App\Filament\User\Resources\Movements\Pages\EditMovement;
use App\Filament\User\Resources\Movements\Pages\ListMovements;
use App\Filament\User\Resources\Movements\Pages\ViewMovement;
use App\Filament\User\Resources\Movements\Schemas\MovementForm;
use App\Filament\User\Resources\Movements\Schemas\MovementInfolist;
use App\Filament\User\Resources\Movements\Tables\MovementsTable;
use App\Models\Movement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'document_id';

    public static function form(Schema $schema): Schema
    {
        return MovementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MovementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovementsTable::configure($table);
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
            'index' => ListMovements::route('/'),
            'create' => CreateMovement::route('/create'),
            'view' => ViewMovement::route('/{record}'),
            'edit' => EditMovement::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();
        return parent::getEloquentQuery()
            ->where('destination_office_id', Auth::user()->office_id);
    }
}
