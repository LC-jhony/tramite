<?php

namespace App\Trait;

use App\Actions\ReceiveDocument;
use App\Enum\DocumentStatus;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

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
                fn (Document $record): bool => ! $record->wasReceived()
            )
            ->disabled(fn (Document $record): bool => $record->wasReceived())
            ->requiresConfirmation()
            ->modalHeading('Recibir Documento')
            ->modalDescription('¿Está seguro de recibir este documento?')
            ->action(function (Document $record) {
                $newStatus = DocumentStatus::EnProceso;
                $currentStatus = DocumentStatus::tryFrom($record->status);

                if ($currentStatus && ! $currentStatus->canTransitionTo($newStatus)) {
                    Notification::make()
                        ->title('Error de transición')
                        ->body("No se puede pasar de {$currentStatus->getLabel()} a {$newStatus->getLabel()}")
                        ->danger()
                        ->send();

                    return;
                }

                app(ReceiveDocument::class)->handle($record);
            })
            ->after(function () use ($livewire) {
                if ($livewire) {
                    $livewire->dispatch('refreshTable');
                }
            });
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
