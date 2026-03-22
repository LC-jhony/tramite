<?php

namespace App\Filament\User\Resources\Documents\Tables;

use App\Trait\HasForwardAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->searchable()
                    ->placeholder('N/A.'),
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
                    ->color(fn(string $state): string => match ($state) {
                        'registrado' => 'info',
                        'en_proceso' => 'warning',
                        'finalizado' => 'success',
                        'rechazado', 'cancelado' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reception_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'registrado' => 'Registrado',
                        'en_proceso' => 'En Proceso',
                        'finalizado' => 'Finalizado',
                        'rechazado' => 'Rechazado',
                        'cancelado' => 'Cancelado',
                    ]),
                SelectFilter::make('type')
                    ->label('Tipo de Documento')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('currentOffice')
                    ->label('Oficina Actual')
                    ->relationship('currentOffice', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('reception_date')
                    ->label('Fecha de Recepción')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('reception_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('reception_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Desde ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Hasta ' . \Carbon\Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                self::getForwardAction()
                    ->label('derivar'),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([5])
            ->striped();
    }
}
