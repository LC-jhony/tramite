<?php

use App\Enum\DocumentStatus;
use App\Models\Document;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->officeA = Office::factory()->create();
    $this->officeB = Office::factory()->create();

    $this->user = User::factory()->create([
        'office_id' => $this->officeA->id,
    ]);
    $this->otherUser = User::factory()->create([
        'office_id' => $this->officeB->id,
    ]);

    $role = Role::create(['name' => 'test-user']);
    $this->user->assignRole($role);
    $this->otherUser->assignRole($role);

    $permissions = [
        'View:Document',
        'Create:Document',
        'Update:Document',
        'Delete:Document',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    $role->givePermissionTo($permissions);
});

describe('DocumentPolicy', function () {
    it('owner can view their own document', function () {
        $document = Document::factory()->create([
            'user_id' => $this->user->id,
            'area_origen_id' => $this->officeA->id,
        ]);

        $policy = new \App\Policies\DocumentPolicy;

        expect($policy->view($this->user, $document))->toBeTrue();
    });

    it('user can view document from their office as origin', function () {
        $document = Document::factory()->create([
            'user_id' => $this->otherUser->id,
            'area_origen_id' => $this->officeA->id,
        ]);

        $policy = new \App\Policies\DocumentPolicy;

        expect($policy->view($this->user, $document))->toBeTrue();
    });

    it('user can view document sent to their office', function () {
        $document = Document::factory()->create([
            'user_id' => $this->otherUser->id,
            'area_origen_id' => $this->officeB->id,
            'id_office_destination' => $this->officeA->id,
        ]);

        $policy = new \App\Policies\DocumentPolicy;

        expect($policy->view($this->user, $document))->toBeTrue();
    });

    it('owner can update their document when status is registered', function () {
        $document = Document::factory()->create([
            'user_id' => $this->user->id,
            'status' => DocumentStatus::REGISTERED,
        ]);

        $policy = new \App\Policies\DocumentPolicy;

        expect($policy->update($this->user, $document))->toBeTrue();
    });

    it('owner can delete their document', function () {
        $document = Document::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $policy = new \App\Policies\DocumentPolicy;

        expect($policy->delete($this->user, $document))->toBeTrue();
    });
});
