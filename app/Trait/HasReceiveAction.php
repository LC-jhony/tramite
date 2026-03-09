<?php

namespace App\Trait;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Models\Document;
use App\Models\DocumentReception;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HasReceiveAction
{
    public static function getReceiveAction($livewire = null): Action
    {
        return Action::make('receive')
            ->label('Recibir')
            ->icon(Heroicon::Inbox)
            ->requiresConfirmation()
            ->modalIcon(Heroicon::CheckCircle)
            ->color('primary')
            ->visible(
                fn(Document $record): bool => ! $record->wasReceived()
            )
            ->disabled(fn(Document $record): bool => $record->wasReceived())
            ->requiresConfirmation()
            ->modalHeading('Recibir Documento')
            ->modalDescription('¿Está seguro de recibir este documento?')
            ->action(function (Document $record) {
                $movement = $record->latestMovement;
                if ($movement) {
                    DocumentReception::create([
                        'document_id' => $record->id,
                        'movement_id' => $movement->id,
                        'user_id' => Auth::id(),
                        'office_id' => Auth::user()->office_id,
                        'reception_date' => now()->toDateString(),
                        'movement_Action' => MovementAction::Recibido->value
                    ]);
                }
            })
            ->after(function () use ($livewire) {
                if ($livewire) {
                    $livewire->dispatch('refreshTable');
                }
            });
        // ->action(fn (Document $record) => self::ReceiveAction($record));
    }

    // public static function ReceiveAction(Document $record): void
    // {
    //     $officeId = Auth::user()?->office_id;

    //     DB::transaction(function () use ($record, $officeId) {
    //         $record->movements()->create([
    //             'from_office_id' => $record->current_office_id,
    //             'to_office_id' => $officeId,
    //             'receipt_date' => now()->toDateString(),
    //             'action' => MovementAction::Recibido->value,
    //             'user_id' => Auth::id(),
    //         ]);

    //         $record->update([
    //             'status' => DocumentStatus::EnProceso->value,
    //         ]);
    //     });
    // }
}
