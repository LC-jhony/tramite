<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateDocumentStatus
{
    /**
     * Handle updating document status (reject, finalize, cancel).
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Document $document, array $data): void
    {
        DB::transaction(function () use ($document, $data) {
            $selectedAction = MovementAction::tryFrom($data['action']);

            $document->movements()->create([
                'from_office_id' => Auth::user()?->office_id,
                'to_office_id' => $document->current_office_id,
                'receipt_date' => now()->toDateString(),
                'action' => $selectedAction->value,
                'user_id' => Auth::id(),
            ]);

            $status = match ($selectedAction) {
                MovementAction::Finalizado => DocumentStatus::Finalizado,
                MovementAction::Cancelado => DocumentStatus::Cancelado,
                MovementAction::Rechazado => DocumentStatus::Rechazado,
                default => DocumentStatus::tryFrom($document->status) ?? DocumentStatus::Registrado,
            };

            $document->update(['status' => $status->value]);
        });
    }
}
