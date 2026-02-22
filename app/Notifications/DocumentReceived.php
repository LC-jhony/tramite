<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\Office;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Document $document,
        public Office $fromOffice
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Documento recibido: '.$this->document->case_number)
            ->greeting('Hola '.$notifiable->name)
            ->line('Ha recibido un nuevo documento.')
            ->line('**Número de Caso:** '.$this->document->case_number)
            ->line('**Asunto:** '.$this->document->subject)
            ->line('**De:** '.$this->fromOffice->name)
            ->action('Ver documento', url('/user/documents/'.$this->document->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'case_number' => $this->document->case_number,
            'subject' => $this->document->subject,
            'from_office' => $this->fromOffice->name,
            'message' => 'Documento recibido de '.$this->fromOffice->name,
            'type' => 'document_received',
        ];
    }
}
