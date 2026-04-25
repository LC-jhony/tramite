<?php

declare(strict_types=1);

namespace App\Trait;

use App\Actions\ForwardDocument;
use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Models\Document;
use App\Models\Office;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

trait HasForwardAction
{
    public static function getForwardAction(): Action
    {
        return Action::make('forward')
            ->color('success')
            ->icon(Heroicon::RocketLaunch)
            ->modalHeading('Derivar Documento')
            ->modalDescription('El documento será enviado a la oficina seleccionada.')
            ->modalSubmitActionLabel('Confirmar Derivación')
            ->form(self::getForwardFormSchema())
            ->visible(
                fn (Document $record): bool => self::canDerive($record)
            )
            ->action(function (Document $record, array $data) {
                $newStatus = match ($data['action']) {
                    MovementAction::Derivado->value => DocumentStatus::EnProceso,
                    MovementAction::Respondido->value => DocumentStatus::Respondido,
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

                self::forwardAction($record, $data);
            })
            ->successNotificationTitle('Documento derivado correctamente');
    }

    public static function getForwardFormSchema(): array
    {
        return [
            Select::make('from_office_id')
                ->label('Oficina de Origen')
                ->options(self::getActiveOffices())
                ->default(fn () => Auth::user()?->office_id)
                ->disabled()
                ->dehydrated()
                ->required(),
            Select::make('to_office_id')
                ->label('Oficina Destino')
                ->options(self::getActiveOffices())
                ->required()
                ->searchable()
                ->placeholder('Selecciona la oficina destino'),
            Select::make('action')
                ->label('Acción')
                ->options([
                    MovementAction::Derivado->value => MovementAction::Derivado->getLabel(),
                    MovementAction::Respondido->value => MovementAction::Respondido->getLabel(),
                ])
                ->required()
                ->native(false)
                ->helperText(new HtmlString('selecione la <strong class="text-primary-600 font-semibold">Acción del Documento</strong> para realizar el tramite '))
                ->hint(new HtmlString('<span class="text-rose-500 text-sm">Selecione la Acción del Tramite que realizara</span>')),
            Textarea::make('observation')
                ->label('Observación')
                ->placeholder('Motivo de la derivación (opcional)')
                ->rows(3)
                ->maxLength(500),
        ];
    }

    public static function getActiveOffices(): Collection
    {
        return Office::where('status', true)->pluck('name', 'id');
    }

    public static function forwardAction(Document $record, array $data): void
    {
        app(ForwardDocument::class)->handle($record, $data);
    }

    public static function canDerive(Document $record): bool
    {
        if ($record->isClosed()) {
            return false;
        }

        $latestMovement = $record->latestMovement;

        if (! $latestMovement) {
            return true;
        }

        if ($latestMovement->user_id === Auth::id()) {
            return false;
        }

        return $latestMovement->to_office_id === Auth::user()?->office_id;
    }
}
