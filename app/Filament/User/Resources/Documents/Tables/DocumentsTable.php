<?php

namespace App\Filament\User\Resources\Documents\Tables;

use App\Trait\HasForwardAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsTable
{
    use HasForwardAction;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->label('Nro. Trámite')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.full_name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('case_number')
                    ->label('Expediente')
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('type.name')
                    ->label('Tipo'),
                TextColumn::make('currentOffice.name')
                    ->label('Oficina Actual')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'recibido' => 'success',
                        'pendiente' => 'warning',
                        'finalizado' => 'info',
                        'rechazado', 'cancelado' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reception_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                self::getForwardAction(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
