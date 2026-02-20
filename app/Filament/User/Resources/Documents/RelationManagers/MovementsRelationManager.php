<?php

namespace App\Filament\User\Resources\Documents\RelationManagers;

use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Filament\User\Resources\Documents\DocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $relatedResource = DocumentResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('originOffice.name')
                    ->label('Oficina Origen')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('originUser.name')
                    ->label('Usuario Origen')
                    ->sortable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('destinationOffice.name')
                    ->label('Oficina Destino')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('destinationUser.name')
                    ->label('Usuario Destino')
                    ->sortable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('action')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (MovementAction $state): ?string => $state->getColor())
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (MovementStatus $state): ?string => $state->getColor())
                    ->sortable(),
                TextColumn::make('receipt_date')
                    ->label('Fecha de Recepción')
                    ->date()
                    ->sortable(),
                TextColumn::make('indication')
                    ->label('Indicación')
                    ->limit(50)
                    ->default('-')
                    ->toggleable(),
                TextColumn::make('observation')
                    ->label('Observación')
                    ->limit(50)
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
