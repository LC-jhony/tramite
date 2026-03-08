<?php

namespace App\Trait;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Models\Document;
use App\Models\Office;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HasForwardAction
{
    public static function getForwardAction(): Action
    {
        return Action::make('forward')
            ->label('Derivar')
            ->color('success')
            ->icon('heroicon-o-paper-airplane')
            ->modalHeading('Derivar Documento')
            ->modalDescription('El documento será enviado a la oficina seleccionada.')
            ->modalSubmitActionLabel('Confirmar Derivación')
            ->form(self::getForwardFormSchema())
            ->disabled(fn (Document $record): bool => $record->wasDerivedBy(auth()->id()))
            ->action(fn (Document $record, array $data) => self::forwardAction($record, $data))
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
            $record->movements()->create([
                'from_office_id' => $data['from_office_id'],
                'to_office_id' => $data['to_office_id'],
                'receipt_date' => now()->toDateString(),
                'action' => MovementAction::Derivado->value,
                'user_id' => Auth::id(),
            ]);

            $record->update([
                // 'current_office_id' => $data['to_office_id'],
                'status' => DocumentStatus::EnProceso->value,
            ]);
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
