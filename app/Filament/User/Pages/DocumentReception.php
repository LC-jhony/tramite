<?php

namespace App\Filament\User\Pages;

use App\Models\Document;
use App\Trait\HasForwardAction;
use App\Trait\HasReceiveAction;
use App\Trait\HasRejectAction;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Auth;

class DocumentReception extends Page implements HasTable
{
    use HasForwardAction, HasReceiveAction, HasRejectAction, InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected string $view = 'filament.user.pages.document-reception';

    protected static ?string $title = 'Recepción de Documentos';

    public function table(Table $table): Table
    {
        $officeId = Auth::user()?->office_id;

        return $table
            ->query(
                Document::query()
                    ->with(['latestMovement', 'latestMovement.toOffice', 'latestMovement.fromOffice'])
                    ->whereHas('movements', function ($query) use ($officeId) {
                        $query->where('to_office_id', $officeId)
                            ->where('action', 'derivado');
                    })
            )
            ->columns([
                TextColumn::make('document_number')
                    ->label('Nro. Trámite')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currentOffice.name')
                    ->label('Remitente')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        default => 'info',
                    }),
                TextColumn::make('type.name')
                    ->label('Tipo'),
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->limit(50),
                TextColumn::make('latestMovement.toOffice.name')
                    ->label('Derivado a')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        default => 'info',
                    }),
                TextColumn::make('reception_date')
                    ->label('Fecha Registro')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                self::getReceiveAction($this),
                self::getForwardAction()
                    ->label('Responder'),
                self::getRejectAction(),
                // ->visible(
                //     fn(Document $record): bool => $record->wasReceived() && ! $record->isClosed()
                // ),
                MediaAction::make('pdf')
                    ->label('documentos')
                    ->icon('bi-file-pdf')
                    ->color('danger')
                    ->media(fn ($record) => $record->file ? asset('storage/'.$record->file) : null),
                // ->visible(fn($record) => !empty($record->file) && auth()->user()->hasAnyRole(['super_admin', 'admin', 'user'])),

            ]);
    }
}
