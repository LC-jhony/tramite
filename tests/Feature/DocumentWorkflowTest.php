<?php

declare(strict_types=1);

use App\Enum\DocumentStatus;
use App\Models\Document;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

it('can register a new document', function () {
    $user = User::factory()->create();
    actingAs($user);

    $document = Document::factory()->create([
        'status' => DocumentStatus::Registrado->value,
    ]);

    assertDatabaseHas(Document::class, [
        'id' => $document->id,
        'status' => DocumentStatus::Registrado->value,
    ]);
});

it('can transition document status', function () {
    $document = Document::factory()->create([
        'status' => DocumentStatus::Registrado->value,
    ]);

    expect($document->status)->toBe(DocumentStatus::Registrado->value);
    
    $document->update(['status' => DocumentStatus::EnProceso->value]);
    
    expect($document->fresh()->status)->toBe(DocumentStatus::EnProceso->value);
});
