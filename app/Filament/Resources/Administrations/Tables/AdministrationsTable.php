<?php

namespace App\Filament\Resources\Administrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdministrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Administraciones')
            ->description('Gestiona las administraciones del sistema.')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('start_period')
                    ->label('Período Inicio')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('end_period')
                    ->label('Período Fin')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('mayor')
                    ->label('Alcalde')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('status')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
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
            ])
            ->emptyStateHeading('No hay administraciones')
            ->emptyStateDescription('Crea la primera administración para comenzar.')
            ->emptyStateIcon('heroicon-o-building-library')
            ->paginationPageOptions([5])
            ->striped();
    }
}
