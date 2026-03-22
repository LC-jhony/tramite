<?php

namespace App\Filament\Resources\Administrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AdministrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
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
                ToggleColumn::make('status')
                    ->label('Estado')
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay administraciones')
            ->emptyStateDescription('Crea la primera administración para comenzar.')
            ->emptyStateIcon('heroicon-o-building-library');
    }
}
