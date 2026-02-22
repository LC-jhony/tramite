<?php

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Models\Administration;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;

beforeEach(function () {
    $office = Office::factory()->create();

    $this->user = User::factory()->create([
        'office_id' => $office->id,
    ]);
    $this->actingAs($this->user);

    \Gate::before(function () {
        return true;
    });
});

it('can create document with factory', function () {
    $document = Document::factory()->create();

    expect($document)->toBeInstanceOf(Document::class);
});

it('document belongs to user', function () {
    $document = Document::factory()->create();

    expect($document->user)->toBeInstanceOf(User::class);
});

it('document belongs to customer', function () {
    $document = Document::factory()->create();

    expect($document->customer)->toBeInstanceOf(Customer::class);
});

it('document belongs to document type', function () {
    $document = Document::factory()->create();

    expect($document->documentType)->toBeInstanceOf(DocumentType::class);
});

it('document belongs to office as origin', function () {
    $document = Document::factory()->create();

    expect($document->areaOrigen)->toBeInstanceOf(Office::class);
});

it('document belongs to administration', function () {
    $document = Document::factory()->create();

    expect($document->gestion)->toBeInstanceOf(Administration::class);
});

it('document has many movements', function () {
    $document = Document::factory()->create();
    $document->movements()->create([
        'origin_office_id' => Office::factory()->create()->id,
        'origin_user_id' => $this->user->id,
        'destination_office_id' => Office::factory()->create()->id,
        'destination_user_id' => $this->user->id,
        'action' => MovementAction::DERIVACION,
        'receipt_date' => now(),
        'status' => MovementStatus::PENDING,
    ]);
    $document->movements()->create([
        'origin_office_id' => Office::factory()->create()->id,
        'origin_user_id' => $this->user->id,
        'destination_office_id' => Office::factory()->create()->id,
        'destination_user_id' => $this->user->id,
        'action' => MovementAction::DERIVACION,
        'receipt_date' => now(),
        'status' => MovementStatus::PENDING,
    ]);

    expect($document->movements)->toHaveCount(2);
});

it('document has many files', function () {
    $document = Document::factory()->create();
    $document->files()->create([
        'filename' => 'file1.pdf',
        'path' => 'documents/file1.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);
    $document->files()->create([
        'filename' => 'file2.pdf',
        'path' => 'documents/file2.pdf',
        'mime_type' => 'application/pdf',
        'size' => 2048,
    ]);

    expect($document->files)->toHaveCount(2);
});

it('document can have registered status', function () {
    $document = Document::factory()->registered()->create();

    expect($document->status)->toBe(DocumentStatus::REGISTERED);
});

it('document can have in process status', function () {
    $document = Document::factory()->inProcess()->create();

    expect($document->status)->toBe(DocumentStatus::IN_PROCESS);
});

it('document can have completed status', function () {
    $document = Document::factory()->completed()->create();

    expect($document->status)->toBe(DocumentStatus::COMPLETED);
});

it('document can have rejected status', function () {
    $document = Document::factory()->rejected()->create();

    expect($document->status)->toBe(DocumentStatus::REJECTED);
});

it('document uses soft deletes', function () {
    expect(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Document::class)))->toBeTrue();
});
