<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => 'Test Customer',
            'dni' => '12345678',
            'ruc' => '12345678901',
            'representation' => false,
            'address' => 'Test Address',
            'phone' => '123456789',
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
