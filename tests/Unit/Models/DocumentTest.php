<?php

declare(strict_types=1);

use App\Models\Document;
use App\Enum\DocumentStatus;
use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can check document status transitions', function () {
    $document = Document::factory()->create([
        'status' => DocumentStatus::Registrado->value,
    ]);

    expect($document->status)->toBe(DocumentStatus::Registrado->value);
    
    $document->update(['status' => DocumentStatus::EnProceso->value]);
    
    expect($document->fresh()->status)->toBe(DocumentStatus::EnProceso->value);
});

it('can check if document is finalized', function () {
    $document = Document::factory()->create([
        'status' => DocumentStatus::Finalizado->value,
    ]);

    expect($document->status)->toBe(DocumentStatus::Finalizado->value);
});

it('has correct status label', function () {
    expect(DocumentStatus::Registrado->getLabel())->toBe('Registrado');
    expect(DocumentStatus::EnProceso->getLabel())->toBe('En Proceso');
    expect(DocumentStatus::Finalizado->getLabel())->toBe('Finalizado');
});
