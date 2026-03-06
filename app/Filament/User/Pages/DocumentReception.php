<?php

namespace App\Filament\User\Pages;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class DocumentReception extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected string $view = 'filament.user.pages.document-reception';

    protected static ?string $title = 'Recepción de Documentos';

    public function table(Table $table): Table
    {
        return $table
            ->query(Document::query()->where('status', 'pendiente')) // Solo documentos pendientes de recibir
            ->columns([
                TextColumn::make('document_number')
                    ->label('Nro. Trámite')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.full_name')
                    ->label('Remitente')
                    ->searchable(),
                TextColumn::make('type.name')
                    ->label('Tipo'),
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->limit(50),
                TextColumn::make('reception_date')
                    ->label('Fecha Registro')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Action::make('receive')
                    ->label('Recibir')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Document $record) => $record->update(['status' => 'recibido'])),
            ]);
    }
}
