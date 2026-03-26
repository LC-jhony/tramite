<?php

namespace App\Filament\User\Widgets;

use App\Filament\User\Resources\Documents\Tables\DocumentsTable;
use App\Models\Document;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentDocumentsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $officeId = Auth::user()?->office_id;

        return Document::query()
            ->with(['customer', 'priority', 'type'])
            ->when($officeId, fn (Builder $query) => $query->where('current_office_id', $officeId))
            ->latest()
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('document_number')
                ->label('Nro. Trámite')
                ->searchable(),
            TextColumn::make('subject')
                ->label('Asunto')
                ->limit(30)
                ->searchable(),
            TextColumn::make('customer.full_name')
                ->label('Cliente')
                ->placeholder('N/A'),
            TextColumn::make('priority.name')
                ->label('Prioridad'),
            TextColumn::make('status')
                ->label('Estado')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'registrado' => 'info',
                    'en_proceso' => 'warning',
                    'finalizado' => 'success',
                    'rechazado', 'cancelado' => 'danger',
                    default => 'gray',
                }),
        ];
    }

    public function getTable(): Table
    {
        return DocumentsTable::configure(parent::getTable());
    }
}
