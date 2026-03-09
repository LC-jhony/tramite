<?php

namespace App\Trait;

use App\Enum\DocumentStatus;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->label('Otro')
            ->color('danger')
            ->icon(Heroicon::XCircle)
            // ->visible(
            //     fn(Document $record): bool => $record->wasReceived() && ! $record->isClosed()
            // )
            ->disabled(
                fn(Document $record): bool => ! $record->wasReceived()
                    || $record->isClosed()
                    || $record->hasActionByCurrentUser()
            )
            ->form(self::getRejectFormSchema())
            ->action(function (Document $record, array $data) {
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
                    'rechazado' => 'rechazado',
                    'finalizado' => 'finalizado',
                    'cancelado' => 'cancelado',
                ])
                ->required()
                ->native(false)
                ->helperText(new HtmlString('selecione la <strong class="text-primary-600 font-semibold">Acción del Documento</strong> para realizar el tramite '))
                ->hint(new HtmlString('<span class="text-rose-500 text-sm">Selecione la Acción del Tramite que realizara</span>')),
            Textarea::make('observation')
                ->label('Motivo de Rechazo')
                ->required()
                ->rows(3),
        ];
    }

    public static function RejectAction(Document $record, array $data): void
    {
        DB::transaction(function () use ($record, $data) {
            $selectedAction = $data['action'];

            $record->movements()->create([
                'from_office_id' => Auth::user()?->office_id,
                'to_office_id' => $record->current_office_id,
                'receipt_date' => now()->toDateString(),
                'action' => $selectedAction,
                'user_id' => Auth::id(),
            ]);

            $status = match ($selectedAction) {
                'finalizado' => DocumentStatus::Finalizado,
                'cancelado' => DocumentStatus::Cancelado,
                default => DocumentStatus::Rechazado,
            };

            $record->update(['status' => $status->value]);
        });
    }

    public static function getNotificationCreate(Document $record): Notification
    {
        return Notification::make()
            ->title('Se Registro el movimiento corectamente')
            ->body("Documento: {$record->document_number}")
            ->success();
    }
}
