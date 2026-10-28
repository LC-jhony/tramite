<?php

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

// Sin RefreshDatabase: esta verificación solo lee contra la base de desarrollo
// (este PHP no tiene el driver pdo_sqlite, por lo que no puede usar :memory:).
beforeEach(function () {
    $this->actingAs(User::firstOrFail());
});

test('la tabla de clientes renderiza los registros con su badge de email', function () {
    $customer = Customer::query()->whereNotNull('email')->firstOrFail();

    $html = Livewire::test(ListCustomers::class)->html();

    expect($html)->toContain($customer->email);
});
