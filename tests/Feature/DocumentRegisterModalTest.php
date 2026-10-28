<?php

use App\Livewire\Document\DocumentRegister;
use App\Models\Customer;
use Livewire\Livewire;

/**
 * El select de usuario crea clientes con el formulario de CustomerForm.
 *
 * @return array<string, mixed>
 */
function mountCustomerOptionForm(): array
{
    $existingIds = Customer::query()->pluck('id')->all();

    $component = Livewire::test(DocumentRegister::class);

    $component->call('mountAction', 'createOption', [], [
        'schemaComponent' => 'form.customer_id',
    ]);

    return [$component, $existingIds];
}

test('la página de registro renderiza el contenedor de modales de Filament', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('wire:partial="action-modals"', false);
});

test('el botón de crear cliente abre el formulario de CustomerForm', function () {
    [$component] = mountCustomerOptionForm();

    expect($component->getMountedActionModalHtml())
        ->toContain('Datos del cliente')
        ->toContain('Tipo de persona')
        ->toContain('Persona Jurídica')
        ->toContain('Información de contacto');
});

test('el formulario de altas crea el cliente con los datos de persona natural', function () {
    [$component, $existingIds] = mountCustomerOptionForm();

    try {
        $component->set('mountedActions.0.data', [
            'representation' => '0',
            'dni' => '41234567',
            'last_name' => 'Vargas Condori',
            'first_name' => 'Maria Elena',
            'full_name' => 'Maria Elena Vargas Condori',
            'phone' => '72581432',
            'email' => 'maria.vargas@example.com',
            'address' => 'Av. Busch N. 1245',
        ]);

        $component->call('callMountedAction');

        expect($component->errors()->all())->toBe([]);

        $customer = Customer::query()->whereNotIn('id', $existingIds)->first();

        expect($customer)
            ->not->toBeNull()
            ->representation->toEqual(0)
            ->dni->toBe('41234567')
            ->last_name->toBe('Vargas Condori')
            ->first_name->toBe('Maria Elena')
            ->full_name->toBe('Maria Elena Vargas Condori')
            ->company->toBeNull()
            ->ruc->toBeNull();

        expect($component->html())
            ->toContain('Maria Elena Vargas Condori');

        expect($component->get('data.customer_id'))->toEqual($customer->id);
    } finally {
        Customer::query()->whereNotIn('id', $existingIds)->delete();
    }
});

test('el formulario de altas crea el cliente con los datos de persona jurídica', function () {
    [$component, $existingIds] = mountCustomerOptionForm();

    try {
        $component->set('mountedActions.0.data', [
            'representation' => '1',
            'dni' => '47654321',
            'ruc' => '2051234567',
            'company' => 'Constructora Andina S.R.L.',
            'full_name' => 'Luis Fernando Salazar Mendoza',
        ]);

        $component->call('callMountedAction');

        expect($component->errors()->all())->toBe([]);

        $customer = Customer::query()->whereNotIn('id', $existingIds)->first();

        expect($customer)
            ->not->toBeNull()
            ->representation->toEqual(1)
            ->ruc->toBe('2051234567')
            ->company->toBe('Constructora Andina S.R.L.')
            ->full_name->toBeNull()
            ->dni->toBeNull()
            ->first_name->toBeNull()
            ->last_name->toBeNull();
    } finally {
        Customer::query()->whereNotIn('id', $existingIds)->delete();
    }
});
