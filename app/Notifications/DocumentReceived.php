<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DocumentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Document $document,
        public string $fromOfficeName,
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
            'icon' => 'hugeicons-document-received',
            'color' => 'success',
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
