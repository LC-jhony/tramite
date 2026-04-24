<?php

namespace App\Actions;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Models\Document;
use App\Models\Office;
use App\Notifications\DocumentDerived;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ForwardDocument
{
    /**
     * Handle the document forwarding logic.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Document $document, array $data): void
    {
        DB::transaction(function () use ($document, $data) {
            $selectedAction = MovementAction::tryFrom($data['action']);
            $fromOffice = Office::find($data['from_office_id']);
            $toOffice = Office::find($data['to_office_id']);

            $document->movements()->create([
                'from_office_id' => $data['from_office_id'],
                'to_office_id' => $data['to_office_id'],
                'receipt_date' => now()->toDateString(),
                'action' => $selectedAction->value,
                'user_id' => Auth::id(),
            ]);

            $status = match ($selectedAction) {
                MovementAction::Derivado => DocumentStatus::EnProceso,
                MovementAction::Respondido => DocumentStatus::Respondido,
                default => DocumentStatus::tryFrom($document->status) ?? DocumentStatus::Registrado,
            };

            $document->update([
                'status' => $status->value,
                'current_office_id' => $data['to_office_id'],
            ]);

            $toOffice->users->each(function ($user) use ($document, $fromOffice, $toOffice, $selectedAction) {
                $user->notify(new DocumentDerived(
                    document: $document,
                    fromOfficeName: $fromOffice->name,
                    toOfficeName: $toOffice->name,
                    action: $selectedAction->value,
                ));
            });
        });
    }
}
