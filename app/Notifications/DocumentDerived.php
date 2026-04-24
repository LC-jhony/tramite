<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DocumentDerived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Document $document,
        public string $fromOfficeName,
        public string $toOfficeName,
        public string $action,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_code' => $this->document->code,
            'document_subject' => $this->document->subject,
            'from_office' => $this->fromOfficeName,
            'to_office' => $this->toOfficeName,
            'action' => $this->action,
            'icon' => 'hugeicons-document-forward',
            'color' => 'info',
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
