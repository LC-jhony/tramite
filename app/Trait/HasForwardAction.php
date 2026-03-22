<?php

namespace App\Trait;

use App\Enum\DocumentStatus;
use App\Models\Document;
use App\Models\Office;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

trait HasForwardAction
{
    public static function getForwardAction(): Action
    {
        return Action::make('forward')
            // ->label('Derivar')
            ->color('success')
            ->icon(Heroicon::RocketLaunch)
            ->modalHeading('Derivar Documento')
            ->modalDescription('El documento será enviado a la oficina seleccionada.')
            ->modalSubmitActionLabel('Confirmar Derivación')
            ->form(self::getForwardFormSchema())
            // ->visible(
            //     fn(Document $record): bool => $record->wasReceived() && ! $record->isClosed()
            // )
            ->disabled(
                fn(Document $record): bool => $record->wasDerivedBy(auth()->id()) ||
                    $record->hasActionByCurrentUser()
            )
            ->action(fn(Document $record, array $data) => self::forwardAction($record, $data))
            ->successNotificationTitle('Documento derivado correctamente');
    }

    public static function getForwardFormSchema(): array
    {
        return [
            Select::make('from_office_id')
                ->label('Oficina de Origen')
                ->options(self::getActiveOffices())
                ->default(fn() => Auth::user()?->office_id)
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
                    'derivado' => 'Derivar',
                    'respondido' => 'Responder',
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

    public static function getActiveOffices(): \Illuminate\Support\Collection
    {
        return Office::where('status', true)->pluck('name', 'id');
    }

    public static function forwardAction(Document $record, array $data): void
    {
        DB::transaction(function () use ($record, $data) {
            $selectedAction = $data['action'];
            $record->movements()->create([
                'from_office_id' => $data['from_office_id'],
                'to_office_id' => $data['to_office_id'],
                'receipt_date' => now()->toDateString(),
                'action' => $selectedAction,
                'user_id' => Auth::id(),
            ]);

            $status = match ($selectedAction) {
                'derivado' => DocumentStatus::EnProceso,
                'respondido' => DocumentStatus::Respondido,
            };

            $record->update(['status' => $status->value]);
        });
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
