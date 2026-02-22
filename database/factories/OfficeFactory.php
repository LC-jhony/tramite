<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeFactory extends Factory
{
    protected $model = Office::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')).'-'.fake()->numerify('###'),
            'name' => fake()->company(),
            'parent_office_id' => null,
            'level' => 1,
            'manager' => fake()->name(),
            'status' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    public function level2(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 2,
        ]);
    }

    public function level3(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 3,
        ]);
    }
}
