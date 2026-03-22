<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisterDocumentCustomer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Document $document;

    public Customer $customer;

    public function __construct(Document $document, Customer $customer)
    {
        $this->document = $document;
        $this->customer = $customer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Confirmación de Registro - Trámite {$this->document->case_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.send-email',
            with: [
                'document' => $this->document,
                'customer' => $this->customer,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
