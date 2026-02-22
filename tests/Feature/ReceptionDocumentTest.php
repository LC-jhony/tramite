<?php

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Movement;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->officeOrigin = Office::factory()->create();
    $this->officeDestination = Office::factory()->create();

    $this->user = User::factory()->create([
        'office_id' => $this->officeDestination->id,
    ]);
    $this->actingAs($this->user);

    $this->customer = Customer::factory()->create();
    $this->documentType = DocumentType::factory()->create();
});

describe('Reception Document Flow', function () {
    it('creates document with registered status', function () {
        $document = Document::factory()->create([
            'customer_id' => $this->customer->id,
            'document_type_id' => $this->documentType->id,
            'area_origen_id' => $this->officeOrigin->id,
            'user_id' => $this->user->id,
            'status' => DocumentStatus::REGISTERED,
        ]);

        expect($document->status)->toBe(DocumentStatus::REGISTERED);
        expect($document->customer)->toBeInstanceOf(Customer::class);
    });

    it('creates initial movement for document', function () {
        $document = Document::factory()->create([
            'customer_id' => $this->customer->id,
            'document_type_id' => $this->documentType->id,
            'area_origen_id' => $this->officeOrigin->id,
            'user_id' => $this->user->id,
        ]);

        $movement = Movement::create([
            'document_id' => $document->id,
            'origin_office_id' => $this->officeOrigin->id,
            'origin_user_id' => $this->user->id,
            'destination_office_id' => $this->officeDestination->id,
            'destination_user_id' => $this->user->id,
            'action' => MovementAction::REGISTRADO,
            'receipt_date' => now(),
            'status' => MovementStatus::PENDING,
        ]);

        expect($movement)->toBeInstanceOf(Movement::class);
        expect($document->movements)->toHaveCount(1);
    });

    it('can derive document to another office', function () {
        $officeC = Office::factory()->create();

        $document = Document::factory()->create([
            'customer_id' => $this->customer->id,
            'document_type_id' => $this->documentType->id,
            'area_origen_id' => $this->officeOrigin->id,
            'id_office_destination' => $this->officeDestination->id,
            'user_id' => $this->user->id,
            'status' => DocumentStatus::REGISTERED,
        ]);

        $document->update(['status' => DocumentStatus::IN_PROCESS]);

        $movement = Movement::create([
            'document_id' => $document->id,
            'origin_office_id' => $this->officeDestination->id,
            'origin_user_id' => $this->user->id,
            'destination_office_id' => $officeC->id,
            'destination_user_id' => $this->user->id,
            'action' => MovementAction::DERIVACION,
            'indication' => 'Derivar para revisión',
            'receipt_date' => now(),
            'status' => MovementStatus::PENDING,
        ]);

        $document->update(['id_office_destination' => $officeC->id]);

        expect($document->status)->toBe(DocumentStatus::IN_PROCESS);
        expect($document->movements)->toHaveCount(1);
        expect($movement->action)->toBe(MovementAction::DERIVACION);
    });

    it('can receive derived document', function () {
        $officeC = Office::factory()->create();

        $document = Document::factory()->create([
            'customer_id' => $this->customer->id,
            'document_type_id' => $this->documentType->id,
            'area_origen_id' => $this->officeOrigin->id,
            'id_office_destination' => $this->officeDestination->id,
            'user_id' => $this->user->id,
            'status' => DocumentStatus::IN_PROCESS,
        ]);

        $movement = Movement::create([
            'document_id' => $document->id,
            'origin_office_id' => $this->officeOrigin->id,
            'origin_user_id' => $this->user->id,
            'destination_office_id' => $this->officeDestination->id,
            'destination_user_id' => $this->user->id,
            'action' => MovementAction::RECIBIDO,
            'receipt_date' => now(),
            'status' => MovementStatus::COMPLETED,
        ]);

        expect($movement->action)->toBe(MovementAction::RECIBIDO);
        expect($movement->status)->toBe(MovementStatus::COMPLETED);
    });

    it('can reject document', function () {
        $document = Document::factory()->create([
            'customer_id' => $this->customer->id,
            'document_type_id' => $this->documentType->id,
            'area_origen_id' => $this->officeOrigin->id,
            'id_office_destination' => $this->officeDestination->id,
            'user_id' => $this->user->id,
            'status' => DocumentStatus::IN_PROCESS,
        ]);

        $movement = Movement::create([
            'document_id' => $document->id,
            'origin_office_id' => $this->officeDestination->id,
            'origin_user_id' => $this->user->id,
            'destination_office_id' => $this->officeOrigin->id,
            'destination_user_id' => $this->user->id,
            'action' => MovementAction::RECHAZADO,
            'observation' => 'Documento incompleto',
            'receipt_date' => now(),
            'status' => MovementStatus::COMPLETED,
        ]);

        $document->update(['status' => DocumentStatus::REJECTED]);

        expect($document->status)->toBe(DocumentStatus::REJECTED);
        expect($movement->action)->toBe(MovementAction::RECHAZADO);
    });

    it('can complete document', function () {
        $document = Document::factory()->create([
            'customer_id' => $this->customer->id,
            'document_type_id' => $this->documentType->id,
            'area_origen_id' => $this->officeOrigin->id,
            'id_office_destination' => $this->officeDestination->id,
            'user_id' => $this->user->id,
            'status' => DocumentStatus::IN_PROCESS,
        ]);

        $movement = Movement::create([
            'document_id' => $document->id,
            'origin_office_id' => $this->officeDestination->id,
            'origin_user_id' => $this->user->id,
            'destination_office_id' => $this->officeOrigin->id,
            'destination_user_id' => $this->user->id,
            'action' => MovementAction::RESPUESTA,
            'receipt_date' => now(),
            'status' => MovementStatus::COMPLETED,
        ]);

        $document->update(['status' => DocumentStatus::COMPLETED]);

        expect($document->status)->toBe(DocumentStatus::COMPLETED);
        expect($movement->action)->toBe(MovementAction::RESPUESTA);
    });

    it('tracks full document lifecycle', function () {
        $document = Document::factory()->create([
            'customer_id' => $this->customer->id,
            'document_type_id' => $this->documentType->id,
            'area_origen_id' => $this->officeOrigin->id,
            'user_id' => $this->user->id,
            'status' => DocumentStatus::REGISTERED,
        ]);

        Movement::create([
            'document_id' => $document->id,
            'origin_office_id' => $this->officeOrigin->id,
            'origin_user_id' => $this->user->id,
            'destination_office_id' => $this->officeDestination->id,
            'destination_user_id' => $this->user->id,
            'action' => MovementAction::REGISTRADO,
            'receipt_date' => now(),
            'status' => MovementStatus::COMPLETED,
        ]);

        $document->update(['status' => DocumentStatus::IN_PROCESS]);

        Movement::create([
            'document_id' => $document->id,
            'origin_office_id' => $this->officeDestination->id,
            'origin_user_id' => $this->user->id,
            'destination_office_id' => $this->officeOrigin->id,
            'destination_user_id' => $this->user->id,
            'action' => MovementAction::RESPUESTA,
            'receipt_date' => now(),
            'status' => MovementStatus::COMPLETED,
        ]);

        $document->update(['status' => DocumentStatus::COMPLETED]);

        expect($document->movements)->toHaveCount(2);
        expect($document->status)->toBe(DocumentStatus::COMPLETED);
    });
});
