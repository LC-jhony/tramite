<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $isCompany = $this->faker->boolean(30);

        return [
            'representation' => $this->faker->boolean,
            'full_name' => $isCompany
                ? $this->faker->company()
                : $this->faker->name(),
            'first_name' => $isCompany ? null : $this->faker->firstName(),
            'last_name' => $isCompany ? null : $this->faker->lastName(),
            'dni' => $isCompany ? null : $this->faker->numerify('########'),
            'phone' => $this->faker->numerify('9########'),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'ruc' => $isCompany ? $this->faker->numerify('###########') : null,
            'company' => $isCompany ? $this->faker->company() : null,
        ];
    }

    public function natural(): static
    {
        return $this->state(fn (array $attributes) => [
            'full_name' => $this->faker->name(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'dni' => $this->faker->numerify('########'),
            'ruc' => null,
            'company' => null,
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'full_name' => $this->faker->company(),
            'first_name' => null,
            'last_name' => null,
            'dni' => null,
            'ruc' => $this->faker->numerify('###########'),
            'company' => $this->faker->company(),
        ]);
    }

    public function withPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => $this->faker->numerify('9########'),
        ]);
    }

    public function withDni(?string $dni = null): static
    {
        return $this->state(fn (array $attributes) => [
            'dni' => $dni ?? $this->faker->numerify('########'),
        ]);
    }

    public function withRuc(?string $ruc = null): static
    {
        return $this->state(fn (array $attributes) => [
            'ruc' => $ruc ?? $this->faker->numerify('###########'),
        ]);
    }

    public function withoutContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => null,
            'email' => null,
            'address' => null,
        ]);
    }
}
