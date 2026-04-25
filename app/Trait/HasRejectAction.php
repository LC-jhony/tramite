<?php

declare(strict_types=1);

namespace App\Trait;

use App\Actions\UpdateDocumentStatus;
use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

trait HasRejectAction
{
    /*
    *  este trait se encarga de  realizar  las siguientes aciones del movimiento del documento
    *  rechazado
    *  finalizado
    *  cancelado
    */
    public static function getRejectAction(): Action
    {
        return Action::make('reject')
            ->label('Finalizar/Rechazar')
            ->color('danger')
            ->icon(Heroicon::XCircle)
            ->disabled(
                fn (Document $record): bool => ! $record->wasReceived()
                    || $record->isClosed()
                    || $record->hasActionByCurrentUser()
            )
            ->form(self::getRejectFormSchema())
            ->action(function (Document $record, array $data) {
                $newStatus = match ($data['action']) {
                    MovementAction::Finalizado->value => DocumentStatus::Finalizado,
                    MovementAction::Rechazado->value => DocumentStatus::Rechazado,
                    MovementAction::Cancelado->value => DocumentStatus::Cancelado,
                    default => null,
                };

                $currentStatus = DocumentStatus::tryFrom($record->status);

                if ($newStatus && $currentStatus && ! $currentStatus->canTransitionTo($newStatus)) {
                    Notification::make()
                        ->title('Error de transición')
                        ->body("No se puede pasar de {$currentStatus->getLabel()} a {$newStatus->getLabel()}")
                        ->danger()
                        ->send();

                    return;
                }

                self::RejectAction($record, $data);
                self::getNotificationCreate($record)
                    ->send();
            });
    }

    public static function getRejectFormSchema(): array
    {
        return [
            Select::make('action')
                ->label('Acción')
                ->options([
                    MovementAction::Rechazado->value => MovementAction::Rechazado->getLabel(),
                    MovementAction::Finalizado->value => MovementAction::Finalizado->getLabel(),
                    MovementAction::Cancelado->value => MovementAction::Cancelado->getLabel(),
                ])
                ->required()
                ->native(false)
                ->helperText(new HtmlString('selecione la <strong class="text-primary-600 font-semibold">Acción del Documento</strong> para realizar el tramite '))
                ->hint(new HtmlString('<span class="text-rose-500 text-sm">Selecione la Acción del Tramite que realizara</span>')),
            Textarea::make('observation')
                ->label('Motivo de la acción')
                ->required()
                ->rows(3),
        ];
    }

    public static function RejectAction(Document $record, array $data): void
    {
        app(UpdateDocumentStatus::class)->handle($record, $data);
    }

    public static function getNotificationCreate(Document $record): Notification
    {
        return Notification::make()
            ->title('Se Registro el movimiento corectamente')
            ->body("Documento: {$record->document_number}")
            ->success();
    }
}
