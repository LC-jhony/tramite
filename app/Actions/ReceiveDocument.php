<?php

namespace App\Actions;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Models\Document;
use App\Models\DocumentReception;
use App\Notifications\DocumentReceived;
use Illuminate\Support\Facades\Auth;

class ReceiveDocument
{
    /**
     * Handle the document reception logic.
     */
    public function handle(Document $document): void
    {
        $movement = $document->latestMovement;

        if ($movement) {
            $fromOffice = $movement->fromOffice;
            $deriverUser = $movement->user;

            DocumentReception::create([
                'document_id' => $document->id,
                'movement_id' => $movement->id,
                'user_id' => Auth::id(),
                'office_id' => Auth::user()->office_id,
                'reception_date' => now()->toDateString(),
                'movement_action' => MovementAction::Recibido->value,
            ]);

            $document->update([
                'status' => DocumentStatus::EnProceso->value,
            ]);

            if ($deriverUser) {
                $deriverUser->notify(new DocumentReceived(
                    document: $document,
                    fromOfficeName: Auth::user()->office?->name ?? 'Oficina Destino',
                ));
            }
        }
    }
}
