<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.id')
                    ->searchable(),
                TextColumn::make('document_number')
                    ->searchable(),
                TextColumn::make('case_number')
                    ->searchable(),
                TextColumn::make('subject')
                    ->searchable(),
                TextColumn::make('origen')
                    ->searchable(),
                TextColumn::make('document_type_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currentOffice.name')
                    ->searchable(),
                TextColumn::make('gestion_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('folio')
                    ->searchable(),
                TextColumn::make('reception_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('response_deadline')
                    ->date()
                    ->sortable(),
                TextColumn::make('condition')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('priority_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
