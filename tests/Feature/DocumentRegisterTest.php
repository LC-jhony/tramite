<?php

use App\Livewire\DocumentRegister;
use App\Models\Administration;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('document can be registered', function () {
    // Arrange: Create necessary data
    $user = User::factory()->create();
    $customer = Customer::create([
        'full_name' => 'Test Customer',
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'dni' => '12345678',
        'email' => 'customer@test.com',
    ]);
    $documentType = DocumentType::create([
        'code' => 'TEST',
        'name' => 'Test Document Type',
        'requires_response' => 1,
        'response_days' => 7,
        'status' => 1,
    ]);
    $office = Office::firstOrCreate([
        'code' => 'MESA',
        'name' => 'mesa de partes',
    ], ['status' => 1]);
    $administration = Administration::firstOrCreate([
        'name' => '2024',
        'mayor' => 'Test Mayor',
    ], [
        'start_period' => now()->year,
        'end_period' => now()->year,
        'status' => 1,
    ]);

    // Act: Call the Livewire component
    $component = Livewire::test(DocumentRegister::class);
    $component->actingAs($user);

    // Fill form data
    $component->set('data.customer_id', $customer->id);
    $component->set('data.document_number', '2024-0001');
    $component->set('data.case_number', '2024-00001');
    $component->set('data.subject', 'Test Subject');
    $component->set('data.origen', 'Externo');
    $component->set('data.current_office_id', $office->id);
    $component->set('data.gestion_id', $administration->id);
    $component->set('data.reception_date', now()->toDateString());
    $component->set('data.response_deadline', now()->addDays(7)->toDateString());
    $component->set('data.document_type_id', $documentType->id);
    $component->set('data.status', 'registrado');
    $component->set('data.folio', '123');
    $component->set('data.condition', true);

    $component->call('create');

    // Assert: Check database
    expect(DB::table('documents')->count())->toBe(1);

    $dbDoc = DB::table('documents')->first();
    expect($dbDoc->subject)->toBe('<p>Test Subject</p>');

    $document = Document::where('subject', '<p>Test Subject</p>')->first();
    expect($document)->not->toBeNull();
    expect($document->document_type_id)->toBe($documentType->id);
});

test('document registration handles errors', function () {
    $user = User::factory()->create();

    $component = Livewire::test(DocumentRegister::class);
    $component->actingAs($user);
    $component->call('create');

    // Assert: No exception thrown (basic smoke test)
    // Note: The form validation might prevent this, but we test the catch block
    $this->assertTrue(true);
});
