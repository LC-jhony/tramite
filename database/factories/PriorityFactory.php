<?php

namespace Database\Factories;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriorityFactory extends Factory
{
    protected $model = Priority::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Alta', 'Media', 'Baja']),
            'color' => fake()->randomElement(['red', 'orange', 'yellow', 'green', 'blue', 'gray']),
            'days' => fake()->randomElement([1, 3, 5, 7, 15, 30]),
            'status' => true,
        ];
    }

    public function alta(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Alta',
            'color' => 'red',
            'days' => 1,
        ]);
    }

    public function media(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Media',
            'color' => 'orange',
            'days' => 5,
        ]);
    }

    public function baja(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Baja',
            'color' => 'green',
            'days' => 15,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
