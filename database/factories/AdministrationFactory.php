<?php

namespace Database\Factories;

use App\Models\Administration;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdministrationFactory extends Factory
{
    protected $model = Administration::class;

    public function definition(): array
    {
        $year = now()->year;

        return [
            'name' => "Gestión {$year}",
            'start_period' => (int) $year,
            'end_period' => (int) $year,
            'mayor' => fake()->name(),
            'status' => true,
        ];
    }

    public function year(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => "Gestión {$year}",
            'start_period' => $year,
            'end_period' => $year,
        ]);
    }
}
