<?php

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

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

it('can render customer list page', function () {
    Customer::factory()->count(5)->create();

    Livewire::test(ListCustomers::class)
        ->assertSuccessful();
});

it('can render create customer page', function () {
    Livewire::test(CreateCustomer::class)
        ->assertSuccessful();
});

it('can render edit customer page', function () {
    $customer = Customer::factory()->create();

    Livewire::test(EditCustomer::class, ['record' => $customer->id])
        ->assertSuccessful();
});

it('can create customer with factory', function () {
    $customer = Customer::factory()->create();

    expect($customer)->toBeInstanceOf(Customer::class);
});

it('can create person type customer', function () {
    $customer = Customer::factory()->create([
        'representation' => false,
    ]);

    expect($customer->representation)->toBeFalse();
});

it('can create company type customer', function () {
    $customer = Customer::factory()->create([
        'representation' => true,
    ]);

    expect($customer->representation)->toBeTrue();
});

it('customer uses soft deletes', function () {
    expect(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Customer::class)))->toBeTrue();
});
