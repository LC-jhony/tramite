<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\Office;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Document $document,
        public Office $fromOffice,
        public ?string $observation = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Documento rechazado: '.$this->document->case_number)
            ->greeting('Hola '.$notifiable->name)
            ->line('Un documento ha sido rechazado.')
            ->line('**Número de Caso:** '.$this->document->case_number)
            ->line('**Asunto:** '.$this->document->subject)
            ->line('**De:** '.$this->fromOffice->name)
            ->error();

        if ($this->observation) {
            $mail->line('**Observación:** '.$this->observation);
        }

        return $mail->action('Ver documento', url('/user/documents/'.$this->document->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'case_number' => $this->document->case_number,
            'subject' => $this->document->subject,
            'from_office' => $this->fromOffice->name,
            'observation' => $this->observation,
            'message' => 'Documento rechazado por '.$this->fromOffice->name,
            'type' => 'document_rejected',
        ];
    }
}
