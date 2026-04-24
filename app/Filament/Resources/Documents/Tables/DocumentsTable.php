<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enum\DocumentStatus;
use App\Trait\HasForwardAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use FinityLabs\FinMail\Actions\SendEmailAction;
use Illuminate\Database\Eloquent\Builder;

class DocumentsTable
{
    use HasForwardAction;

    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Documentos')
            ->description('Gestiona los documentos del sistema.')
            ->columns([
                TextColumn::make('document_number')
                    ->label('Nro. Documento')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('case_number')
                    ->label('Nro. Expediente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type.name')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer.full_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('No description.'),
                TextColumn::make('origen')
                    ->label('Origen')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'interno' => 'info',
                        'externo' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('currentOffice.name')
                    ->label('Oficina Actual')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('reception_date')
                    ->label('Fecha Recepción')
                    ->date('d/m/Y')
                    ->sortable(),
                // TextColumn::make('response_deadline')
                //     ->label('Fecha Límite')
                //     ->date('d/m/Y')
                //     ->sortable()
                //     ->badge(),
                // ->color(fn ($record): string => $record->response_deadline && $record->response_deadline->isPast() && ! $record->isClosed() ? 'danger' : 'gray'),
                TextColumn::make('folio')
                    ->label('Folio')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => DocumentStatus::tryFrom($state)?->getColor() ?? 'gray')
                    ->icon(fn (string $state): ?string => DocumentStatus::tryFrom($state)?->getIcon()),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'registrado' => 'Registrado',
                        'en_proceso' => 'En Proceso',
                        'derivado' => 'Derivado',
                        'finalizado' => 'Finalizado',
                        'cancelado' => 'Cancelado',
                        'rechazado' => 'Rechazado',
                    ])
                    ->multiple(),
                // SelectFilter::make('condition')
                //     ->label('Condición')
                //     ->options([
                //         'urgente' => 'Urgente',
                //         'pendiente' => 'Pendiente',
                //         'atrasado' => 'Atrasado',
                //     ])
                //     ->multiple(),
                SelectFilter::make('document_type_id')
                    ->label('Tipo de Documento')
                    ->relationship('type', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('current_office_id')
                    ->label('Oficina Actual')
                    ->relationship('currentOffice', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('gestion_id')
                    ->label('Gestión')
                    ->relationship('administration', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('origen')
                    ->label('Origen')
                    ->options([
                        'interno' => 'Interno',
                        'externo' => 'Externo',
                    ]),
                Filter::make('reception_date')
                    ->label('Fecha Recepción')
                    ->schema([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('reception_date', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('reception_date', '<=', $v));
                    })
                    ->columns(2),
            ])
            ->recordActions([
                self::getForwardAction()
                    ->label('Derivar'),
                EditAction::make(),
                SendEmailAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay documentos')
            ->emptyStateDescription('Registra el primer documento para comenzar.')
            ->emptyStateIcon(Heroicon::DocumentArrowDown)
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->defaultSort('reception_date', 'desc');
    }
}
