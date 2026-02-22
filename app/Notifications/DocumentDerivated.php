<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\Office;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentDerivated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Document $document,
        public Office $toOffice,
        public ?string $indication = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Documento derivado: '.$this->document->case_number)
            ->greeting('Hola '.$notifiable->name)
            ->line('Un documento ha sido derivado a otra oficina.')
            ->line('**Número de Caso:** '.$this->document->case_number)
            ->line('**Asunto:** '.$this->document->subject)
            ->line('**Hacia:** '.$this->toOffice->name);

        if ($this->indication) {
            $mail->line('**Indicación:** '.$this->indication);
        }

        return $mail->action('Ver documento', url('/user/documents/'.$this->document->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'case_number' => $this->document->case_number,
            'subject' => $this->document->subject,
            'to_office' => $this->toOffice->name,
            'indication' => $this->indication,
            'message' => 'Documento derivado a '.$this->toOffice->name,
            'type' => 'document_derivated',
        ];
    }
}
