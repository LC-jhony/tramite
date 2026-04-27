<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentResource\Pages\CreateDocument;
use App\Filament\Resources\DocumentResource\Pages\EditDocument;
use App\Filament\Resources\DocumentResource\Pages\ListDocuments;
use App\Models\Document;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('can list documents', function () {
    $user = User::factory()->create();
    actingAs($user);

    livewire(ListDocuments::class)
        ->assertSuccessful();
});

it('can create document', function () {
    $user = User::factory()->create();
    actingAs($user);

    $data = [
        'subject' => 'Test Document',
        'origen' => 'externo',
    ];

    livewire(CreateDocument::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasNoFormErrors();
});

it('can update document', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();
    actingAs($user);

    livewire(EditDocument::class, ['record' => $document->id])
        ->fillForm(['subject' => 'Updated Subject'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($document->fresh()->subject)->toBe('Updated Subject');
});
