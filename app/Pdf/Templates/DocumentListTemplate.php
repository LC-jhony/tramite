<?php

namespace App\Pdf\Templates;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentListTemplate
{
    protected array $data = [];

    public function make(): static
    {
        return new static;
    }

    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function render(): \Barryvdh\DomPDF\PDF
    {
        $html = Blade::render('pdf.documents', $this->data);

        return Pdf::loadHTML($html)
            ->setPaper('A4', 'landscape');
    }

    public function toBase64(): string
    {
        return base64_encode($this->render()->output());
    }

    public function livewireDownload(string $filename): StreamedResponse
    {
        return $this->render()
            ->download($filename);
    }
}
