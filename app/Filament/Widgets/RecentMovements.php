<?php

namespace App\Filament\Widgets;

use App\Models\Movement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentMovements extends TableWidget
{
    protected function getTableHeading(): ?string
    {
        return 'Últimos Movimientos';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Movement::query()
                    ->with(['document', 'originOffice', 'destinationOffice'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('document.case_number')
                    ->label('Caso')
                    ->searchable()
                    ->limit(20),
                TextColumn::make('originOffice.name')
                    ->label('Origen')
                    ->limit(20),
                TextColumn::make('destinationOffice.name')
                    ->label('Destino')
                    ->limit(20),
                TextColumn::make('action')
                    ->label('Acción')
                    ->badge()
                    ->limit(20),
                TextColumn::make('receipt_date')
                    ->label('Fecha')
                    ->date(),
            ]);
    }
}
